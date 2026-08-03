import React, { useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales';
import {weekDayList, availabilitySeries, availabilityTypes, repeat, formatTime} from '../helpers'
moment.locale('de')

const ACTION_LABELS = {
    created: 'Erstellt',
    updated: 'Geändert',
    deleted: 'Gelöscht',
    dldb_slot_update: 'DLDB Slotlänge'
}

const formatChangedAt = (value) => {
    if (!value) {
        return ''
    }
    const date = new Date(String(value).replace(' ', 'T'))
    if (isNaN(date.getTime())) {
        return value
    }
    const pad = (n) => (n < 10 ? `0${n}` : String(n))
    return `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()}`
        + ` ${pad(date.getHours())}:${pad(date.getMinutes())}`
}

const AvailabilityHistoryPanel = ({ historyUrl, availabilityId }) => {
    const [status, setStatus] = useState('loading')
    const [rows, setRows] = useState([])

    useEffect(() => {
        let cancelled = false
        setStatus('loading')
        setRows([])

        const separator = String(historyUrl).includes('?') ? '&' : '?'
        const url = `${historyUrl}${separator}availabilityId=${encodeURIComponent(availabilityId)}`

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { Accept: 'application/json' }
        }).then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`)
            }
            return response.json()
        }).then((payload) => {
            if (cancelled) {
                return
            }
            const data = payload.data || []
            setRows(data)
            setStatus(data.length ? 'ready' : 'empty')
        }).catch(() => {
            if (!cancelled) {
                setStatus('error')
            }
        })

        return () => {
            cancelled = true
        }
    }, [historyUrl, availabilityId])

    if (status === 'loading') {
        return <p className="availability-history-status">Wird geladen…</p>
    }
    if (status === 'error') {
        return <p className="availability-history-status">Änderungsverlauf konnte nicht geladen werden.</p>
    }
    if (status === 'empty') {
        return <p className="availability-history-status">Kein Änderungsverlauf für diese Öffnungszeit.</p>
    }

    return (
        <ul className="list--table availability-history-list">
            {rows.map((row) => (
                <li key={row.id}>
                    <div className="cell cell--meta">
                        {(ACTION_LABELS[row.action] || row.action)
                            + ' · ' + formatChangedAt(row.changedAt)
                            + (row.changedBy ? ` · ${row.changedBy}` : '')}
                    </div>
                    <div className="cell cell--summary">{row.summary || ''}</div>
                </li>
            ))}
        </ul>
    )
}

AvailabilityHistoryPanel.propTypes = {
    historyUrl: PropTypes.string.isRequired,
    availabilityId: PropTypes.oneOfType([PropTypes.number, PropTypes.string]).isRequired
}

const TableBodyLayout = (props) => {
    const {
        onDelete,
        onSelect,
        onAbort,
        availabilityList,
        data,
        historyUrl,
        canViewAvailabilityHistory
    } = props
    const [openHistoryId, setOpenHistoryId] = useState(null)

    return (
        <div className="table-responsive-wrapper">
            <table className="table--base">
                <thead>
                    <tr>
                        <th></th>
                        <th>Wochentage</th>
                        <th>Serie</th>
                        <th>Von</th>
                        <th>Bis</th>
                        <th>Uhrzeit</th>
                        <th>Typ</th>
                        <th>Zeitschlitz</th>
                        <th>Arbeitsplätze</th>
                        <th>Buchung</th>
                        <th>Anmerkung</th>
                    </tr>
                </thead>
                <tbody>
                    {renderTable(
                        onDelete,
                        onSelect,
                        onAbort,
                        availabilityList,
                        data,
                        canViewAvailabilityHistory,
                        historyUrl,
                        openHistoryId,
                        setOpenHistoryId
                    )}
                </tbody>
            </table>
            <style>{`
                .availability-history-list .cell--meta {
                    white-space: nowrap;
                    color: #555;
                    font-size: 0.9em;
                }
                .availability-history-list .cell--summary {
                    word-break: break-word;
                }
                .availability-history-row td {
                    background: #f7f7f7;
                    padding: 0.75rem 1rem;
                }
                .availability-history-status {
                    margin: 0;
                }
            `}</style>
        </div>
    )
}

/* eslint-disable complexity */
const renderTable = (
    onDelete,
    onSelect,
    onAbort,
    availabilityList,
    data,
    canViewAvailabilityHistory,
    historyUrl,
    openHistoryId,
    setOpenHistoryId
) => {
    if (availabilityList.length > 0) {
        return availabilityList.map((availability, key) => {

            const startDate = moment(availability.startDate, 'X').format('DD.MM.YYYY');
            const endDate = moment(availability.endDate, 'X').format('DD.MM.YYYY');
            const startTime = formatTime(availability.startTime);
            const endTime = formatTime(availability.endTime);

            const titleEdit = `Bearbeiten von ${availability.id} (${startDate} - ${endDate})`
            const titleDelete = `Löschen von ${availability.id} (${startDate} - ${endDate})`
            const titleAbort = `Die aktuelle Beabeitung wird zurückgesetzt.`
            const titleDisabled = `Diese Aktion ist während einer aktuellen Bearbeitung nicht möglich.`
            const titleHistory = `Änderungsverlauf von ${availability.id}`

            if (! availability.id && ! availability.tempId) {
                availability.tempId = `spontaneous_ID_${key}`
            }

            const onClickEdit = ev => {
                ev.preventDefault()
                onSelect(availability)
            }

            const onClickDelete = ev => {
                ev.preventDefault()
                onDelete(availability)
            }

            const onClickAbort = ev => {
                ev.preventDefault()
                onAbort()
            }

            const onClickHistory = ev => {
                ev.preventDefault()
                setOpenHistoryId(openHistoryId == availability.id ? null : availability.id)
            }

            const availabilityWeekDayList = Object.keys(availability.weekday).
                filter(key => parseInt(availability.weekday[key], 10) > 0)
            
            const availabilityWeekDay = weekDayList.
                filter(element => availabilityWeekDayList.includes(element.value)).map(item => item.label).join(', ')

            const availabilityRepeat = availabilitySeries.
                find(element => element.value == repeat(availability.repeat)).name

            const availabilityType = availabilityTypes.
                find(element => element.value == availability.type)

            const disabled = (
                (availability.id && availability.__modified) || 
                (availability.tempId && availability.__modified)
            ); 

            const isSelected = Boolean(data && (
                (data.id && availability.id == data.id) || 
                (data.tempId && availability.tempId == data.tempId)
            ));

            const showHistoryButton = Boolean(
                canViewAvailabilityHistory && historyUrl && availability.id
            )
            const historyOpen = showHistoryButton && openHistoryId == availability.id

            return (
                <React.Fragment key={availability.id || availability.tempId || key}>
                    <tr
                        aria-selected={isSelected}
                        style={(() => {
                            const hasDescriptionText = (text) =>
                                availability?.description?.includes(text);

                            if (availability?.kind === 'exclusion' || hasDescriptionText('Ausnahme')) {
                                return { backgroundColor: '#FFE05B' };
                            }

                            return null;
                        })()}
                    >
                        <td className="center" style={{ whiteSpace: "nowrap" }}>
                            <span style={{ marginRight: "5px" }}>
                                <a href="#" className="icon" aria-label="Bearbeiten" title={titleEdit} onClick={onClickEdit}>
                                    <i className="fas fa-pencil-alt" aria-hidden="true"></i>
                                </a>
                            </span>
                            <span>
                                {disabled ?
                                    <i className="far fa-trash-alt" title={titleDisabled}></i> :
                                    <a href="#" className="icon" title={titleDelete} aria-label={titleDelete} onClick={onClickDelete}>
                                        <i className="far fa-trash-alt" aria-hidden="true"></i>
                                    </a>
                                }
                            </span>
                            {disabled ?
                                <span style={{ marginLeft: "5px" }}>
                                    <a href="#" className="icon" title={titleAbort} aria-label="abbrechen" onClick={onClickAbort}>
                                        <i className="fas fa-ban" aria-hidden="true"></i>
                                    </a>
                                </span>
                                : null
                            }
                            {showHistoryButton ?
                                <span style={{ marginLeft: "5px" }}>
                                    <a
                                        href="#"
                                        className="icon"
                                        title={titleHistory}
                                        aria-label={titleHistory}
                                        aria-expanded={historyOpen ? 'true' : 'false'}
                                        onClick={onClickHistory}
                                    >
                                        <i className="fas fa-history" aria-hidden="true"></i>
                                    </a>
                                </span>
                                : null
                            }
                        </td>
                        <td>
                            {availabilityWeekDay}
                        </td>
                        <td>
                            {availabilityRepeat}
                        </td>
                        <td>
                            {startDate}
                        </td>
                        <td>
                            {endDate}
                        </td>
                        <td>
                            {startTime} - {endTime}
                        </td>
                        <td>
                            {availabilityType && availabilityType.name ? availabilityType.name : ""}
                        </td>
                        <td>
                            {availability.slotTimeInMinutes}min
                        </td>
                        <td>
                            {availability.workstationCount.intern}/{availability.workstationCount.public}
                        </td>
                        <td>
                            {(availability.bookable?.startInDays !== undefined && availability.bookable?.startInDays !== null && availability.bookable?.startInDays !== ''
                                ? availability.bookable.startInDays
                                : availability.scope?.preferences?.appointment?.startInDaysDefault || 0)}-
                            {(availability.bookable?.endInDays !== undefined && availability.bookable?.endInDays !== null && availability.bookable?.endInDays !== ''
                                ? availability.bookable.endInDays
                                : availability.scope?.preferences?.appointment?.endInDaysDefault || 60)}
                        </td>
                        <td>
                            {availability.description ? availability.description : '-'}
                        </td>
                    </tr>
                    {historyOpen ?
                        <tr className="availability-history-row">
                            <td colSpan={11}>
                                <strong>Änderungsverlauf</strong>
                                <AvailabilityHistoryPanel
                                    historyUrl={historyUrl}
                                    availabilityId={availability.id}
                                />
                            </td>
                        </tr>
                        : null
                    }
                </React.Fragment>
            )
        })
    }
}

TableBodyLayout.propTypes = {
    availabilityList: PropTypes.array,
    data: PropTypes.object,
    onSelect: PropTypes.func.isRequired,
    onDelete: PropTypes.func.isRequired,
    onAbort: PropTypes.func.isRequired,
    historyUrl: PropTypes.string,
    canViewAvailabilityHistory: PropTypes.bool
}

export default TableBodyLayout

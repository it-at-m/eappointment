import React, { useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales';
import {weekDayList, availabilitySeries, availabilityTypes, repeat, formatTime} from '../helpers'
import { AvailabilityHistoryPanel } from './historyShared'
moment.locale('de')

const TableBodyLayout = (props) => {
    const {
        onDelete,
        onSelect,
        onAbort,
        availabilityList,
        data,
        historyUrl,
        canViewAvailabilityHistory,
        historyRefreshKey
    } = props
    const [openHistoryId, setOpenHistoryId] = useState(null)

    useEffect(() => {
        if (openHistoryId == null) {
            return
        }
        const stillPresent = (availabilityList || []).some(
            (availability) => availability.id == openHistoryId
        )
        if (!stillPresent) {
            setOpenHistoryId(null)
        }
    }, [availabilityList, openHistoryId])

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
                        historyRefreshKey,
                        openHistoryId,
                        setOpenHistoryId
                    )}
                </tbody>
            </table>
            <style>{`
                .availability-history-table-wrap {
                    overflow-x: auto;
                }
                .availability-history-table {
                    margin-top: 0.5rem;
                    font-size: 0.9em;
                }
                .availability-history-table .cell--meta {
                    white-space: nowrap;
                    color: #555;
                    line-height: 1.35;
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
    historyRefreshKey,
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
                                    refreshKey={historyRefreshKey}
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
    canViewAvailabilityHistory: PropTypes.bool,
    historyRefreshKey: PropTypes.oneOfType([PropTypes.number, PropTypes.string])
}

export default TableBodyLayout

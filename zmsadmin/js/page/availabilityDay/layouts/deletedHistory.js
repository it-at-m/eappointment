import React, { useEffect, useState, Fragment } from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import {
    ACTION_LABELS,
    formatChangedAt,
    formatWeekdays,
    AvailabilityHistoryPanel
} from './historyShared'

const DeletedAvailabilityHistory = ({ historyUrl, refreshKey }) => {
    const [status, setStatus] = useState('loading')
    const [rows, setRows] = useState([])
    const [openHistoryId, setOpenHistoryId] = useState(null)

    useEffect(() => {
        if (!historyUrl) {
            setStatus('empty')
            setRows([])
            return undefined
        }

        let cancelled = false
        setStatus('loading')
        setRows([])

        const separator = String(historyUrl).includes('?') ? '&' : '?'
        const url = `${historyUrl}${separator}action=deleted`

        new Promise((resolve, reject) => {
            $.ajax(url, { method: 'GET' })
                .done((payload) => resolve(payload))
                .fail((xhr) => reject(new Error(`HTTP ${xhr.status}`)))
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
    }, [historyUrl, refreshKey])

    useEffect(() => {
        if (openHistoryId == null) {
            return
        }
        const stillPresent = rows.some((row) => row.availabilityId == openHistoryId)
        if (!stillPresent) {
            setOpenHistoryId(null)
        }
    }, [rows, openHistoryId])

    return (
        <section className="availability-history board board--spaceless" style={{ marginTop: '1.5rem' }}>
            <div className="board__header">
                <h2 className="board__heading heading">Gelöschte Öffnungszeiten</h2>
            </div>
            <div className="board__body body">
                {status === 'loading' ?
                    <p className="availability-history-status">Wird geladen…</p> : null}
                {status === 'error' ?
                    <p className="availability-history-status">
                        Gelöschte Öffnungszeiten konnten nicht geladen werden.
                    </p> : null}
                {status === 'empty' ?
                    <p className="availability-history-status">
                        Keine gelöschten Öffnungszeiten im gewählten Zeitraum.
                    </p> : null}
                {status === 'ready' ?
                    <div className="table-responsive-wrapper availability-history-table-wrap">
                        <table className="table--base availability-history-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Aktion</th>
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
                                {rows.map((row) => {
                                    const availabilityId = row.availabilityId
                                    const canExpand = Boolean(historyUrl && availabilityId)
                                    const historyOpen = canExpand && openHistoryId == availabilityId
                                    const titleHistory = `Änderungsverlauf von ${availabilityId}`

                                    const onClickHistory = (ev) => {
                                        ev.preventDefault()
                                        if (!canExpand) {
                                            return
                                        }
                                        setOpenHistoryId(historyOpen ? null : availabilityId)
                                    }

                                    return (
                                        <Fragment key={row.id}>
                                            <tr>
                                                <td className="center" style={{ whiteSpace: 'nowrap' }}>
                                                    {canExpand ?
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
                                                        : null}
                                                </td>
                                                <td className="cell--meta">
                                                    <div>{ACTION_LABELS[row.action] || row.action}</div>
                                                    <div>{formatChangedAt(row.changedAt)}</div>
                                                    {row.changedBy ? <div>{row.changedBy}</div> : null}
                                                </td>
                                                <td>{formatWeekdays(row.weekday) || '–'}</td>
                                                <td>{row.series || '–'}</td>
                                                <td>{row.validFrom || '–'}</td>
                                                <td>{row.validTo || '–'}</td>
                                                <td>{row.timeRange || '–'}</td>
                                                <td>{row.type || '–'}</td>
                                                <td>{row.slotTime || '–'}</td>
                                                <td>{row.workstations || '–'}</td>
                                                <td>{row.bookable || '–'}</td>
                                                <td>{row.description || '–'}</td>
                                            </tr>
                                            {historyOpen ?
                                                <tr className="availability-history-row">
                                                    <td colSpan={12}>
                                                        <strong>Änderungsverlauf</strong>
                                                        <AvailabilityHistoryPanel
                                                            historyUrl={historyUrl}
                                                            availabilityId={availabilityId}
                                                            refreshKey={refreshKey}
                                                        />
                                                    </td>
                                                </tr>
                                                : null}
                                        </Fragment>
                                    )
                                })}
                            </tbody>
                        </table>
                    </div> : null}
            </div>
            <style>{`
                .availability-history-table .cell--meta {
                    white-space: nowrap;
                    color: #555;
                    line-height: 1.35;
                }
                .availability-history-row td {
                    background: #f7f7f7;
                    padding: 0.75rem 1rem;
                }
            `}</style>
        </section>
    )
}

DeletedAvailabilityHistory.propTypes = {
    historyUrl: PropTypes.string,
    refreshKey: PropTypes.oneOfType([PropTypes.number, PropTypes.string])
}

export default DeletedAvailabilityHistory

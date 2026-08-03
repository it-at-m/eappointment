import React, { useEffect, useState } from 'react'
import PropTypes from 'prop-types'

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

/**
 * Scope-wide list of deleted Öffnungszeiten for tech admins (ZMSKVR-1249 option 3).
 */
const DeletedAvailabilityHistory = ({ historyUrl, refreshKey }) => {
    const [status, setStatus] = useState('loading')
    const [rows, setRows] = useState([])

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
    }, [historyUrl, refreshKey])

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
                                {rows.map((row) => (
                                    <tr key={row.id}>
                                        <td className="cell--meta">
                                            <div>{ACTION_LABELS[row.action] || row.action}</div>
                                            <div>{formatChangedAt(row.changedAt)}</div>
                                            {row.changedBy ? <div>{row.changedBy}</div> : null}
                                        </td>
                                        <td>{row.weekdays || '–'}</td>
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
                                ))}
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
            `}</style>
        </section>
    )
}

DeletedAvailabilityHistory.propTypes = {
    historyUrl: PropTypes.string,
    refreshKey: PropTypes.oneOfType([PropTypes.number, PropTypes.string])
}

export default DeletedAvailabilityHistory

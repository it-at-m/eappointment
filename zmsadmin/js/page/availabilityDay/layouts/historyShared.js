import React, { useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import { weekDayList } from '../helpers'

const loadHistory = (url) => new Promise((resolve, reject) => {
    $.ajax(url, { method: 'GET' })
        .done((payload) => resolve(payload))
        .fail((xhr) => reject(new Error(`HTTP ${xhr.status}`)))
})

export const ACTION_LABELS = {
    created: 'Erstellt',
    updated: 'Geändert',
    deleted: 'Gelöscht',
    dldb_slot_update: 'DLDB Slotlänge'
}

export const formatChangedAt = (value) => {
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

export const formatWeekdays = (weekday) => {
    if (!weekday || typeof weekday !== 'object') {
        return ''
    }
    return weekDayList
        .filter((element) => parseInt(weekday[element.value], 10) > 0)
        .map((item) => item.label)
        .join(', ')
}

export const HistoryRowsTable = ({ rows }) => (
    <div className="availability-history-table-wrap">
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
                ))}
            </tbody>
        </table>
    </div>
)

HistoryRowsTable.propTypes = {
    rows: PropTypes.array.isRequired
}

export const AvailabilityHistoryPanel = ({ historyUrl, availabilityId, refreshKey }) => {
    const [status, setStatus] = useState('loading')
    const [rows, setRows] = useState([])

    useEffect(() => {
        let cancelled = false
        setStatus('loading')
        setRows([])

        const separator = String(historyUrl).includes('?') ? '&' : '?'
        const url = `${historyUrl}${separator}availabilityId=${encodeURIComponent(availabilityId)}`

        loadHistory(url).then((payload) => {
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
    }, [historyUrl, availabilityId, refreshKey])

    if (status === 'loading') {
        return <p className="availability-history-status">Wird geladen…</p>
    }
    if (status === 'error') {
        return <p className="availability-history-status">Änderungsverlauf konnte nicht geladen werden.</p>
    }
    if (status === 'empty') {
        return <p className="availability-history-status">Kein Änderungsverlauf für diese Öffnungszeit.</p>
    }

    return <HistoryRowsTable rows={rows} />
}

AvailabilityHistoryPanel.propTypes = {
    historyUrl: PropTypes.string.isRequired,
    availabilityId: PropTypes.oneOfType([PropTypes.number, PropTypes.string]).isRequired,
    refreshKey: PropTypes.oneOfType([PropTypes.number, PropTypes.string])
}

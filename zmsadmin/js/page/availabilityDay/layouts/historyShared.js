import React, { useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import moment from 'moment'
import { weekDayList, availabilitySeries, availabilityTypes, repeat, formatTime } from '../helpers'

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

const isZeroTime = (value) => {
    if (value === null || value === undefined || value === '') {
        return true
    }
    const normalized = String(value).trim()
    return normalized === '00:00:00' || normalized === '00:00' || normalized === '0:00' || normalized === '0'
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

export const formatHistoryDate = (value) => {
    if (!value) {
        return ''
    }
    const parsed = moment(value, ['YYYY-MM-DD', 'DD.MM.YYYY'], true)
    return parsed.isValid() ? parsed.format('DD.MM.YYYY') : String(value)
}

export const formatHistorySeries = (row) => {
    const value = repeat({
        afterWeeks: parseInt(row.everyXWeeks, 10) || 0,
        weekOfMonth: parseInt(row.everyOtherWeek, 10) || 0
    })
    const found = availabilitySeries.find((element) => element.value == value)
    return found ? found.name : ''
}

export const formatHistoryType = (row) => {
    const type = isZeroTime(row.appointmentStartTime) ? 'openinghours' : 'appointment'
    const found = availabilityTypes.find((element) => element.value === type)
    return found ? found.name : ''
}

export const formatHistoryTimeRange = (row) => {
    const useAppointment = !isZeroTime(row.appointmentStartTime)
    const startLabel = formatTime(useAppointment ? row.appointmentStartTime : row.startTime)
    const endLabel = formatTime(useAppointment ? row.appointmentEndTime : row.endTime)
    if (!startLabel && !endLabel) {
        return ''
    }
    return `${startLabel} - ${endLabel}`
}

export const formatHistorySlotTime = (row) => {
    const parsed = moment(row.timeSlot, ['HH:mm:ss', 'HH:mm', 'H:mm:ss', 'H:mm'], true)
    if (!parsed.isValid()) {
        return ''
    }
    return `${parsed.hours() * 60 + parsed.minutes()}min`
}

export const formatHistoryWorkstations = (row) => {
    const intern = parseInt(row.appointmentWorkstationCount, 10)
    if (Number.isNaN(intern)) {
        return ''
    }
    const reduction = parseInt(row.internetReduction, 10) || 0
    return `${intern}/${Math.max(0, intern - reduction)}`
}

export const formatHistoryBookable = (row) => {
    if (row.openFromDays === null || row.openFromDays === undefined || row.openFromDays === '') {
        return ''
    }
    if (row.openUntilDays === null || row.openUntilDays === undefined || row.openUntilDays === '') {
        return ''
    }
    return `${row.openFromDays}-${row.openUntilDays}`
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
                        <td>{formatHistorySeries(row) || '–'}</td>
                        <td>{formatHistoryDate(row.startDate) || '–'}</td>
                        <td>{formatHistoryDate(row.endDate) || '–'}</td>
                        <td>{formatHistoryTimeRange(row) || '–'}</td>
                        <td>{formatHistoryType(row) || '–'}</td>
                        <td>{formatHistorySlotTime(row) || '–'}</td>
                        <td>{formatHistoryWorkstations(row) || '–'}</td>
                        <td>{formatHistoryBookable(row) || '–'}</td>
                        <td>{row.comment || '–'}</td>
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

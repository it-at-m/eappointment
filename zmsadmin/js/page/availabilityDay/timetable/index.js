
import React from 'react'
import ConflictTimeBar from '../widgets/timeBars/conflict'
import AppointmentTimeBar from '../widgets/timeBars/appointment'
import NumberOfAppointmentsTimeBar from '../widgets/timeBars/numberOfAppointments'
import OpeningTimebar from '../widgets/timeBars/opening'
import { timeToFloat } from '../../../lib/utils'

export const headerMiddle = () => {
    return (
        <span className="middle"></span>
    )
}

export const headerRight = (links) => {
    return (
        <span className="right">
            <a href={links.monthView}>zurück zur Monatsansicht</a>
        </span>
    )
}

export const renderConflicts = (conflicts) => {return conflicts
    .map((data, key) => <ConflictTimeBar key={key} {...{ key, data }} />)}

export const renderNumberOfAppointments = (items, maxWorkstationCount) => {return items
    .filter(item => item.type === 'appointment')
    .map((data, key) => <NumberOfAppointmentsTimeBar key={key} {...{ key, data, maxWorkstationCount }} />)}


export const renderAppointments = (items, maxWorkstationCount, onSelect) => {return items
    .filter(item => item.type === 'appointment')
    .map((data, key) => <AppointmentTimeBar key={key} {...{ key, data, maxWorkstationCount, onSelect }} />)}


// Overlapping availabilities get their own lane, so no bar hides another one
export const getLanes = (items) => {
    const laneEnds = []
    const lanes = new Map()
    const sorted = [...items].sort((a, b) =>
        timeToFloat(a.startTime) - timeToFloat(b.startTime) || timeToFloat(a.endTime) - timeToFloat(b.endTime))
    sorted.forEach(item => {
        const start = timeToFloat(item.startTime)
        let lane = laneEnds.findIndex(end => end <= start)
        if (lane === -1) {
            lane = laneEnds.length
        }
        laneEnds[lane] = timeToFloat(item.endTime)
        lanes.set(item, lane)
    })
    return lanes
}

export const renderOpenings = (items, onSelect) => {
    const openings = items.filter(item => item.type === "openinghours")
    const lanes = getLanes(openings)
    return openings.map((data, key) => <OpeningTimebar key={key} {...{ key, data, onSelect, lane: lanes.get(data) }} />)
}

export const renderFooter = () => {}
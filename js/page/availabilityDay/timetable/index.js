
import React from 'react'
import ConflictTimeBar from '../widgets/timeBars/conflict'
import AppointmentTimeBar from '../widgets/timeBars/appointment'
import NumberOfAppointmentsTimeBar from '../widgets/timeBars/numberOfAppointments'
import OpeningTimebar from '../widgets/timeBars/opening'

export const headerMiddle = () => {
    return (
        <span className="middle"></span>
    )
}

export const headerRight = (links, onNewClick) => {
    return (
        <span className="right">
            <a href={links.monthView}>zurück zur Monatsansicht</a>
            <button className="button button--diamond button-new" onClick={onNewClick}>neue Öffnungszeit</button>
        </span>
    )
}

export const renderConflicts = (conflicts) => {return conflicts
    .map((data, key) => <ConflictTimeBar key={key} {...{ key, data }} />)}

export const renderNumberOfAppointments = (items) => {return items
    .filter(item => item.type === 'appointment')
    .map((data, key) => <NumberOfAppointmentsTimeBar key={key} {...{ key, data }} />)}


export const renderAppointments = (items, maxWorkstationCount, onSelect) => {return items
    .filter(item => item.type === 'appointment')
    .map((data, key) => <AppointmentTimeBar key={key} {...{ key, data, maxWorkstationCount, onSelect }} />)}


export const renderOpenings = (items, onSelect) => {return items
    .filter(item => item.type === "openinghours")
    .map((data, key) => <OpeningTimebar key={key} {...{ key, data, onSelect }} />)}

export const renderFooter = () => {return <small>Zum Bearbeiten einer Öffnungszeit, bitte auf den entsprechenden blauen oder grünen Zeitstrahl klicken.</small>}
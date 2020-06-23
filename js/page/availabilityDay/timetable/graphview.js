import React from 'react'
import PropTypes from 'prop-types'

import moment from 'moment/min/moment-with-locales';
moment.locale('de')

import Board from '../layouts/board'
import TimeTableBodyLayout from '../layouts/timeTableBody'
import calendarNavigation from '../widgets/calendarNavigation'
import ConflictTimeBar from '../widgets/timeBars/conflict'
import AppointmentTimeBar from '../widgets/timeBars/appointment'
import NumberOfAppointmentsTimeBar from '../widgets/timeBars/numberOfAppointments'
import OpeningTimebar from '../widgets/timeBars/opening'

const headerMiddle = () => {
    return (
        <span className="middle"></span>
    )
}

const headerRight = (links, onNewClick) => {
    return (
        <span className="right">
            <a href={links.monthView}>zurück zur Monatsansicht</a>
            <button className="button button--diamond button-new" onClick={onNewClick}>neue Öffnungszeit</button>
        </span>
    )
}

const renderConflicts = conflicts => conflicts
    .map((data, key) => <ConflictTimeBar key={key} {...{ key, data }} />)

const renderNumberOfAppointments = (items) => items
    .filter(item => item.type === 'appointment')
    .map((data, key) => <NumberOfAppointmentsTimeBar key={key} {...{ key, data }} />)


const renderAppointments = (items, maxWorkstationCount, onSelect) => items
    .filter(item => item.type === 'appointment')
    .map((data, key) => <AppointmentTimeBar key={key} {...{ key, data, maxWorkstationCount, onSelect }} />)


const renderOpenings = (items, onSelect) => items
    .filter(item => item.type === "openinghours")
    .map((data, key) => <OpeningTimebar key={key} {...{ key, data, onSelect }} />)

const renderFooter = () => <small>Zum Bearbeiten einer Öffnungszeit, bitte auf den entsprechenden blauen oder grünen Zeitstrahl klicken.</small>

const GraphView = (props) => {
    const { onSelect, timestamp } = props;
    const titleTime = moment(timestamp, 'X').format('dddd, DD.MM.YYYY')
    const timeTableBody = <TimeTableBodyLayout
        showConflicts={props.conflicts.length > 0}
        conflicts={renderConflicts(props.conflicts)}
        appointments={renderAppointments(props.availabilities, props.maxWorkstationCount, onSelect)}
        numberOfAppointments={renderNumberOfAppointments(props.availabilityListSlices)}
        openings={renderOpenings(props.availabilities, onSelect)}
    />
    return (
        <Board className="board--light availability-timetable"
            title={titleTime}
            titleAside={calendarNavigation(props.links)}
            headerRight={headerRight(props.links, props.onNewAvailability)}
            headerMiddle={headerMiddle()}
            body={timeTableBody}
            footer={renderFooter()} />
    )
}

GraphView.defaultProps = {
    onNewAvailability: () => { },
    availabilities: [],
    conflicts: []
}

GraphView.propTypes = {
    timestamp: PropTypes.number,
    links: PropTypes.object,
    onNewAvailability: PropTypes.func,
    conflicts: PropTypes.array,
    availabilities: PropTypes.array,
    availabilityListSlices: PropTypes.array,
    maxWorkstationCount: PropTypes.number,
    onSelect: PropTypes.func.isRequired
}

export default GraphView


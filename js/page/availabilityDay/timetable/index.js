import React, { PropTypes } from 'react'
import moment from 'moment'

import Board from '../layouts/board'
import TimeTableBodyLayout from '../layouts/timeTableBody'
import calendarNavigation from '../widgets/calendarNavigation'

import ConflictTimeBar from '../widgets/timeBars/conflict'
import AppointmentTimeBar from '../widgets/timeBars/appointment'
import NumberOfAppointmentsTimeBar from '../widgets/timeBars/numberOfAppointments'
import OpeningTimebar from '../widgets/timeBars/opening'

const headerRight = (links, onNewClick) => {
    return(
        <span>
            <a href={links.monthView}>zurück zur Monatsansicht</a>
            <button className="button-new" onClick={onNewClick}>neue Öffnungszeit</button>
        </span>
    )
}

const renderConflicts = (availabilities) => availabilities
    .filter(availability => availability.type === 'conflict')
    .map(data => <ConflictTimeBar key={data.id} {...{ data }} />)

const renderNumberOfAppointments = (items) => items
    .filter(item => item.type === 'numberOfAppointments')
    .map(data => <NumberOfAppointmentsTimeBar key={data.id} {...{ data }} />)


const renderAppointments = (items, maxWorkstationCount, onSelect) => items
    .filter(item => item.type === 'appointment')
    .map(data => <AppointmentTimeBar key={data.id} {...{ data, maxWorkstationCount, onSelect }} />)


const renderOpenings = (items, onSelect) => items
    .filter(item => item.type === "openinghours")
    .map(data => <OpeningTimebar key={data.id} {...{ data, onSelect }} />)

const renderFooter = () => <small>Zum Bearbeiten einer Öffnungszeit, bitte auf den entsprechenden blauen oder grünen Zeitstrahl klicken.</small>


const TimeTable = (props) => {
    const { onSelect, timestamp } = props;
    const titleTime = moment(timestamp).format('dddd, DD.MM.YYYY')

    const timeTableBody = <TimeTableBodyLayout
                              showConflicts={props.conflicts.length > 0}
                              conflicts={renderConflicts(props.conflicts)}
                              appointments={renderAppointments(props.availabilities, props.maxWorkstationCount, onSelect)}
                              numberOfAppointments={renderNumberOfAppointments(props.availabilities, onSelect)}
                              openings={renderOpenings(props.availabilities, onSelect)}
                          />

    return (
        <Board className="availability-timetable"
            title={titleTime}
            titleAside={calendarNavigation(props.links)}
            headerRight={headerRight(props.links, props.onNewAvailability)}
            body={timeTableBody}
            footer={renderFooter()} />
    )
}

TimeTable.defaultProps = {
    onNewAvailability: () => {},
    availabilities: [],
    conflicts: []
}

TimeTable.propTypes = {
    timestamp: PropTypes.number,
    links: PropTypes.object,
    onNewAvailability: PropTypes.func,
    conflicts: PropTypes.array,
    availabilities: PropTypes.array,
    maxWorkstationCount: PropTypes.number,
    onSelect: PropTypes.func.isRequried
}

export default TimeTable


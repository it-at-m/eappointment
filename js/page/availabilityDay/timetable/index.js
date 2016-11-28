import React, { PropTypes } from 'react'
import moment from 'moment'

import Board from '../layouts/board'
import TimeTableBodyLayout from '../layouts/timeTableBody'
import calendarNavigation from '../widgets/calendarNavigation'

import TimeBar from '../widgets/timeBar'

const headerRight = (links, onNewClick) => {
    return(
        <span>
            <a href={links.monthView}>zurück zur Monatsansicht</a>
            <button className="button-new" onClick={onNewClick}>neue Öffnungszeit</button>
        </span>
    )
}

const renderConflicts = conflicts => conflicts.map(conflict => {
    return <TimeBar type="conflict" data={conflict} />
})

const TimeTable = (props) => {
    const titleTime = moment(props.timestamp).format('dddd, DD.MM.YYYY')

    const timeTableBody = <TimeTableBodyLayout
                              showConflicts={props.conflicts.length > 0}
                              conflicts={renderConflicts(props.conflicts)}/>

    return (
        <Board className="availability-timetable"
            title={titleTime}
            titleAside={calendarNavigation(props.links)}
            headerRight={headerRight(props.links, props.onNewAvailability)}
            body={timeTableBody}
            footer="" />
    )
}

TimeTable.defaultProps = {
    onNewAvailability: () => {},
    conflicts: []
}

TimeTable.propTypes = {
    timestamp: PropTypes.number,
    links: PropTypes.object,
    onNewAvailability: PropTypes.func,
    conflicts: PropTypes.array
}

export default TimeTable


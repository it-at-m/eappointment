import React from 'react'
import PropTypes from 'prop-types'

import moment from 'moment/min/moment-with-locales';
moment.locale('de')

import Board from '../layouts/board'
import GraphBodyLayout from '../layouts/graphBody'
import calendarNavigation from '../widgets/calendarNavigation'
import * as constants from './index.js'
import { Workload } from '../widgets/workload.js';

const GraphView = (props) => {
    const { onSelect, timestamp } = props;
    const titleTime = moment(timestamp, 'X').format('dddd, DD.MM.YYYY')
    const graphBody = <GraphBodyLayout
        showConflicts={props.conflicts.length > 0}
        conflicts={constants.renderConflicts(props.conflicts)}
        appointments={constants.renderAppointments(props.availabilityList, props.maxWorkstationCount, onSelect)}
        numberOfAppointments={constants.renderNumberOfAppointments(props.availabilityList, props.maxWorkstationCount)}
        openings={constants.renderOpenings(props.availabilityList, onSelect)} 
    />
    return (
        <>
            <Workload slotBuckets={props.slotBuckets} />
            <Board className="board--light availability-timetable"
                title={titleTime}
                titleAside={calendarNavigation(props.links)}
                headerRight={constants.headerRight(props.links, props.onNewAvailability)}
                headerMiddle={constants.headerMiddle()}
                body={graphBody}
                footer={constants.renderFooter()}
            />

        </>
    )
}

GraphView.defaultProps = {
    onNewAvailability: () => { },
    availabilityList: [],
    conflicts: []
}

GraphView.propTypes = {
    timestamp: PropTypes.number,
    links: PropTypes.object,
    onNewAvailability: PropTypes.func,
    conflicts: PropTypes.array,
    availabilityList: PropTypes.array,
    maxWorkstationCount: PropTypes.number,
    onSelect: PropTypes.func.isRequired
}

export default GraphView


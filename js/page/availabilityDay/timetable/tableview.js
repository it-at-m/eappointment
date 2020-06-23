import React from 'react'
import PropTypes from 'prop-types'

import moment from 'moment/min/moment-with-locales';
moment.locale('de')

import Board from '../layouts/board'
//import TimeTableBodyLayout from '../layouts/timeTableBody'
import calendarNavigation from '../widgets/calendarNavigation'
import * as constants from './index.js'

const TableView = (props) => {
    const { onSelect, timestamp } = props;
    const titleTime = moment(timestamp, 'X').format('dddd, DD.MM.YYYY')
    return (
        <Board className="board--light availability-timetable"
            title={titleTime}
            titleAside={calendarNavigation(props.links)}
            headerRight={constants.headerRight(props.links, props.onNewAvailability)}
            headerMiddle={constants.headerMiddle()}
            body='test'
            footer={constants.renderFooter()}
        />
    )
}

TableView.defaultProps = {

}

TableView.propTypes = {
    timestamp: PropTypes.number,
    links: PropTypes.object,
    onNewAvailability: PropTypes.func,
    onSelect: PropTypes.func.isRequired
}

export default TableView


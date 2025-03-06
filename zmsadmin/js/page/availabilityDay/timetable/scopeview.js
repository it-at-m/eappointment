import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales'
moment.locale('de')

import Board from '../layouts/board'
import TableBodyLayout from '../layouts/tableBody'
import calendarNavigation from '../widgets/calendarNavigation'
import * as constants from './index.js'

const ScopeView = (props) => {
    const { onDelete, onSelect, onAbort, timestamp, availabilityList, data } = props
    const titleTime = `Alle Öffnungszeiten für den Standort ${props.scope?.contact?.name || ''}`
    
    const TableBody = <TableBodyLayout
        availabilityList={availabilityList}
        data={data}
        onDelete={onDelete}
        onSelect={onSelect}
        onAbort={onAbort}
        showAllDates={true}
    />
    
    return (
        <Board className="board--light availability-timetable"
            title={titleTime}
            titleAside={calendarNavigation(props.links)}
            headerRight={constants.headerRight(props.links, props.onNewAvailability)}
            headerMiddle={constants.headerMiddle()}
            body={TableBody}
            footer=""
        />
    )
}

ScopeView.propTypes = {
    timestamp: PropTypes.number,
    links: PropTypes.object,
    scope: PropTypes.object,
    data: PropTypes.object,
    availabilityList: PropTypes.array,
    onNewAvailability: PropTypes.func,
    onDelete: PropTypes.func.isRequired,
    onSelect: PropTypes.func.isRequired,
    onAbort: PropTypes.func.isRequired
}

export default ScopeView
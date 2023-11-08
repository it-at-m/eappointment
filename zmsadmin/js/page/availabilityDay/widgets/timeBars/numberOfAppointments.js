import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'
import propTypeAvailability from '../../../../lib/propTypeAvailability'
import { timeToFloat } from '../../../../lib/utils'

const NumberOfAppointments = props => {
    const { data, maxWorkstationCount } = props
    const startTime = moment(data.startTime, 'hh:mm:ss').format('HH:mm');
    const endTime = moment(data.endTime, 'hh:mm:ss').format('HH:mm');
    const title = (data.description) ? `${data.description}, ${startTime} - ${endTime}` : `${startTime} - ${endTime}`
                   
    const timeItemStart = timeToFloat(data.startTime)
    const timeItemEnd = timeToFloat(data.endTime)
    const timeItemLength = timeItemEnd - timeItemStart


    const busy = data.busySlots
    const max = data.maxSlots

    const heightEm = maxWorkstationCount > 0
        ? data.workstationCount.intern * 1.6 / maxWorkstationCount
        : 0

    const busyBarStyle = {
        height: `${Math.round(100 * busy/max, 2)}%`
    }

    const style = {
        height: `${heightEm}em`,
        left: `${timeItemStart}em`,
        width: `${timeItemLength}em`
    }
    return (
        <div className="item-bar" {... { title, style }}>
            <div style={busyBarStyle} className="busy-bar"></div>
            <span className="item-bar_inner">{busy}/{max}</span>
        </div>
    )
}

NumberOfAppointments.propTypes = {
    data: propTypeAvailability,
    selectedAvailability: propTypeAvailability,
    maxWorkstationCount: PropTypes.number
}

export default NumberOfAppointments

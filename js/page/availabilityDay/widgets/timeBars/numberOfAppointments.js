import React from 'react'

import propTypeAvailability from '../../../../lib/propTypeAvailability'
import { timeToFloat } from '../../../../lib/utils'

const NumberOfAppointments = props => {
    const { data } = props
    const title = `${data.description}, ${data.startTime} - ${data.endTime}`

    const timeItemStart = timeToFloat(data.startTime)
    const timeItemEnd = timeToFloat(data.endTime)
    const timeItemLength = timeItemEnd - timeItemStart

    const style = {
        left: `${timeItemStart}em`,
        width: `${timeItemLength}em`
    }

    const busy = data.busySlots
    const max = data.maxSlots

    const busyBarStyle = {
        height: `${Math.round(100 * busy/max, 2)}%`
    }


    return (
        <div className="item-bar" {... { title, style }}>
            <div style={busyBarStyle} className="busy-bar"></div>
            <span className="item-bar_inner">{busy}/{max}</span>
        </div>
    )
}

NumberOfAppointments.propTypes = {
    data: propTypeAvailability
}

export default NumberOfAppointments

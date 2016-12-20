import React, { PropTypes } from 'react'
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

    return (
        <div className="item-bar" {... { title, style }}>
            <span className="item-bar_inner">{busy}/{max}</span>
        </div>
    )
}

NumberOfAppointments.propTypes = {
    data: PropTypes.shape({
        type: PropTypes.oneOf(['appointment'])
    })
}

export default NumberOfAppointments

import React, { PropTypes } from 'react'
import { timeToFloat } from '../../../../lib/utils'
import moment from 'moment'

const Conflict = props => {
    const { data } = props

    const timeItemStart = timeToFloat(data.startTime)
    const timeItemEnd = timeToFloat(data.endTime)
    const timeItemLength = timeItemEnd - timeItemStart

    const firstAppointmentTime = moment(data.appointments[0].date)

    const title = `${data.amendment} ${firstAppointmentTime.format('HH:mm')}`

    const style = {
        left: `${firstAppointmentTime.format('HH')}em`,
        width: `${timeItemLength}em`
    }

    return (
        <div className="item-bar" {...{ title, style}} >
            <span className="item-bar_inner">⚡</span>
        </div>
    )
}

Conflict.propTypes = {
    data: PropTypes.shape({
        type: PropTypes.oneOf(['conflict'])
    })
}

export default Conflict

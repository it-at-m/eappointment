import React from 'react'

import propTypeConflict from '../../../../lib/propTypeConflict'
import { timestampToFloat } from '../../../../lib/utils'
import moment from 'moment'

const Conflict = props => {
    const { data } = props

    const appointment = data.appointments[0]
    const availability = appointment.availability || {}
    const slotTime = availability.slotTimeInMinutes || 0

    const timeItemStart = timestampToFloat(appointment.date)
    const timeItemEnd = timestampToFloat(appointment.date + slotTime * 60 * appointment.slotCount)
    const timeItemLength = timeItemEnd - timeItemStart

    const firstAppointmentTime = moment(data.appointments[0].date, 'X')

    const title = `${data.amendment} ${firstAppointmentTime.format('HH:mm')}`

    const style = {
        left: `${timeItemStart}em`,
        width: `${timeItemLength}em`
    }

    return (
        <div className="item-bar" {...{ title, style }} >
            <span className="item-bar_inner">⚡</span>
        </div>
    )
}

Conflict.propTypes = {
    data: propTypeConflict
}

export default Conflict

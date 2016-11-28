import React, { PropTypes } from 'react'
import moment from 'moment'

const timeToFloat = (time) => {
    const momentTime = moment(time)

    return momentTime.hours() + (momentTime.minutes() / 60)
}

const TimeBar = (props) => {
    const { type, data } = props

    const timeItemStart = timeToFloat(data.startTime)
    const timeItemEnd = timeToFloat(data.endTime)
    const timeItemLength = timeItemEnd - timeItemStart

    console.log(type, data)
    console.log(timeItemStart, timeItemEnd)

    const firstAppointmentTime = moment(data.appointments[0].date)

    const title = `${data.amendment} ${firstAppointmentTime.format('HH:mm')}`

    const style = {
        left: `${firstAppointmentTime.format('HH')}em`,
        width: `${timeItemLength}em`
    }

    if (type === 'conflict') {
        return (
            <div className="item-bar" {...{ title, style}} >
                <span className="item-bar_inner">⚡</span>
            </div>
        )
    }
}

TimeBar.propTypes = {
    type: PropTypes.string,
    data: PropTypes.object
}

export default TimeBar

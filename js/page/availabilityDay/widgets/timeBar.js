import React, { PropTypes } from 'react'
import moment from 'moment'

const timeToFloat = (time) => {
    const momentTime = moment(time)

    return momentTime.hours() + (momentTime.minutes() / 60)
}

const TimeBar = (props) => {
    const { type, data, heightEm } = props

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

    if (type === 'numberOfAppointments') {
        return null //TODO
    }

    if (type === 'appointment') {
        const title = `${data.description}, ${data.startTime} - ${data.endTime} - Intern:${data.workstationCount.intern} / Public: ${data.workstationCount.public} / Callcenter: ${data.workstationCount.callcenter}`
        const style = {
            height: `${heightEm}em`,
            left: `${timeItemStart}em`,
            widht: `${timeItemLength}em`
        }

        return (
            <a href="#" className="item-bar" {...{title, style}}>
                <span className="item-bar_inner">{data.workstationCount.intern}</span>
            </a>
        )
    }
}

TimeBar.defaultProps = {
    heightEm: 0
}

TimeBar.propTypes = {
    type: PropTypes.string,
    data: PropTypes.object,
    heightEm: PropTypes.number
}

export default TimeBar

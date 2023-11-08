import React from 'react'
import PropTypes from 'prop-types'

import moment from 'moment'
import { range } from '../lib/utils'

const padTwo = value => {
    const strValue = `${value}`
    return strValue.length >= 2 ? strValue : `0${strValue}`
}

const renderTimeOption = (value, key) => {
    return <option key={key} value={value}>{padTwo(value)}</option>
}

const renderQuarterOption = (quarter, key) => {
    return <option key={key} value={`q${quarter}`}>{padTwo(quarter)}</option>
}

const renderHours = () => {
    return range(0, 23).map(renderTimeOption)
}

const renderMinutes = () => {
    return range(0, 59).map(renderTimeOption)
}

const renderQuarters = () => {
    return range(0, 45, 15).map(renderQuarterOption)
}

const TimePicker = (props) => {
    const time = moment(props.value, props.format)

    const onHourChange = ev => {
        const newHour = parseInt(ev.target.value, 10)
        props.onChange(time.clone().hour(newHour).format(props.format))
    }

    const onMinuteChange = ev => {
        const newMinute = parseInt(ev.target.value.replace(/^q/, ''), 10)
        props.onChange(time.clone().minute(newMinute).format(props.format))
    }

    const selectedHour = `${time.hour()}`
    const minute = time.minute()
    const minutePrefix = minute % 15 === 0 ? 'q' : ''
    const selectedMinute = `${minutePrefix}${minute}`
    const nameHour = props.name + '--hour'
    const nameMinute = props.name + '--minute'

    return (
        <span className="timepicker">
            <select className="form-control" name={nameHour} onChange={onHourChange} value={selectedHour}>
                {renderHours()}
            </select>
            <span className="delimiter">{":"}</span>
            <select className="form-control" name={nameMinute} onChange={onMinuteChange} value={selectedMinute}>
                <optgroup>
                    {renderQuarters()}
                </optgroup>
                <optgroup>
                    {renderMinutes()}
                </optgroup>
            </select>
        </span>
    )
}

TimePicker.defaultProps = {
    value: moment(),
    format: 'HH:mm:ss',
    onChange: () => {}
}

TimePicker.propTypes = {
    name: PropTypes.string,
    value: PropTypes.string,
    format: PropTypes.string,
    onChange: PropTypes.func
}

export default TimePicker

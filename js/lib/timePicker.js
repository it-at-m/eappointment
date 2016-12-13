import React, { PropTypes } from 'react'
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

    return (
        <span className="timepicker">
            <select onChange={onHourChange} value={selectedHour}>
                {renderHours()}
            </select>
            {":"}
            <select onChange={onMinuteChange} value={selectedMinute}>
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
    value: PropTypes.string,
    format: PropTypes.string,
    onChange: PropTypes.func
}

export default TimePicker

/* eslint-disable react/prop-types */
import React from 'react'
import DatePicker from 'react-datepicker'
import moment from 'moment'

const toSeconds = millis => Math.floor(parseInt(millis, 10) / 1000)
const toMilliseconds = seconds => 1000 * parseInt(seconds, 10)

export const Date = ({name, value, onChange}) => {
    const onPick = (date) => {
        onChange(name, toSeconds(date.format('x')))
    }

    return (
        <div className="add-date-picker">
            <DatePicker selected={moment(toMilliseconds(value), 'x')} onChange={onPick} />
        </div>
    )
}

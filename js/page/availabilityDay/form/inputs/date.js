/* eslint-disable react/prop-types */
import React from 'react'
import DatePicker from 'react-datepicker'
import moment from 'moment'

export const Date = ({name, value, onChange}) => {
    const onPick = (date) => {
        onChange(name, date.format('X'))
    }

    return (
        <div className="add-date-picker">
            <DatePicker selected={moment(value, 'X')} onChange={onPick} />
        </div>
    )
}

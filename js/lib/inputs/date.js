/* eslint-disable react/prop-types */
import React from 'react'
import DatePicker from 'react-datepicker'
import moment from 'moment'

export const Date = ({name, value, onChange, attributes = {}}) => { 
    const onPick = (date) => {
        onChange(name, moment(date, 'X').unix())
    }

    return (
        <div className="add-date-picker" {...attributes}>
            <DatePicker  className="form-control form-input" dateFormat="dd.MM.yyyy" selected={moment.unix(value).toDate()} onChange={onPick} {...{ name }} />
        </div>
    )
}

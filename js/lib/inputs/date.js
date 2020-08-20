/* eslint-disable react/prop-types */
import React from 'react'
import moment from 'moment'
import DatePicker, { registerLocale } from 'react-datepicker'
import de from 'date-fns/locale/de'
registerLocale('de', de)

export const Date = ({name, value, onChange, attributes = {}}) => { 
    const onPick = (date) => {
        onChange(name, moment(date, 'X').unix())
    }

    return (
        <div className="add-date-picker" {...attributes}>
            <DatePicker locale="de" className="form-control form-input" dateFormat="dd.MM.yyyy" selected={moment.unix(value).toDate()} onChange={onPick} {...{ name }} />
        </div>
    )
}


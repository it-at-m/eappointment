/* eslint-disable react/prop-types */
import React from 'react'
import TimePicker from 'rc-time-picker'
import moment from 'moment'

const TIME_FORMAT = 'HH:mm:ss'

export const Time = ({ name, value, onChange }) => {
    const onPick = time => {
        onChange(name, time.format(TIME_FORMAT))
    }

    return <TimePicker defaultValue={moment(value, TIME_FORMAT)} onChange={onPick} />
}

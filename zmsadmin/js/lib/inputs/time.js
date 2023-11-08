/* eslint-disable react/prop-types */
import React from 'react'
// import TimePicker from 'rc-time-picker'
import TimePicker from '../timePicker.js'

const TIME_FORMAT = 'HH:mm:ss'

export const Time = ({ name, value, onChange }) => {
    const onPick = time => {
        onChange(name, time)
    }
    return <TimePicker name={name} value={value} onChange={onPick} format={TIME_FORMAT}/>
}

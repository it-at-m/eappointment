/* eslint-disable react/prop-types */
import React from 'react'

const noOp = () => {}

const SelectOption = ({name, value}) => {
    return (
        <option {...{ value }}>{name || value}</option>
    )
}

const SelectOptGroup = ({label, options = [], selectedValue}) => {
    return (
        <optgroup {...{ label }}>{renderOptions(options, selectedValue)}</optgroup>
    )
}

const renderOptions = (options) => options.map((option, key) => <SelectOption {...option} {...{ key }} />)
const renderGroups = (groups) => groups.map((group, key) => <SelectOptGroup {...group} {...{ key }} />)

export const Select = ({name, options = [], groups = [], value, onChange = noOp, attributes = {}}) => {
    return (
        <select {...{ name, onChange }} defaultValue={value} {...attributes}>
            {renderGroups(groups)}
            {renderOptions(options)}
        </select>
    )
}

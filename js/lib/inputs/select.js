/* eslint-disable react/prop-types */
import React from 'react'

const noOp = () => { }

const SelectOption = ({ name, value, title }) => {
    return (
        <option {...{ value }} {...{ title }}>{name || value}</option>
    )
}

const SelectOptGroup = ({ label, options = [], selectedValue }) => {
    return (
        <optgroup {...{ label }}>{renderOptions(options, selectedValue)}</optgroup>
    )
}

const renderOptions = (options) => options.map((option, key) => <SelectOption key={key} {...option} {...{ key }} />)
const renderGroups = (groups) => groups.map((group, key) => <SelectOptGroup key={key} {...group} {...{ key }} />)

export const Select = ({ name, options = [], groups = [], value, onChange = noOp, attributes = {} }) => {
    const onSelect = ev => onChange(name, ev.target.value)

    return (
        <select onChange={onSelect} {...{ name }} value={value} {...attributes} className="form-control">
            {renderGroups(groups)}
            {renderOptions(options)}
        </select>
    )
}

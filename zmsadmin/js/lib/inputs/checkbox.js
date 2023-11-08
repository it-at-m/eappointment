/* eslint-disable react/prop-types */
import React from 'react'
import PropTypes from 'prop-types'
import { noOp } from '../utils'

export const Checkbox = ({ name, value, label, checked = "false", disabled=false, onChange = noOp, attributes = {}, className = 'form-check-input' }) => {

    const onInput = () => {
        //ev.preventDefault()
        onChange(name, !checked)
    }

    attributes.checked = (checked) ? "checked" : "";
    return (
        <label className="form-check-label" {...{ label }}>
            <input type="checkbox" value={value || ""} {...{ name }} {...attributes} onChange={onInput} className={className} disabled={disabled} />
            {label}
        </label>
    )
}

export const CheckboxGroup = (props) => {
    const toggle = (toggleValue) => {
        const oldValue = props.value
        const newValue = (oldValue.indexOf(toggleValue) > -1)
            ? oldValue.filter(v => v !== toggleValue)
            : oldValue.concat([toggleValue])

        props.onChange(props.name, newValue)
    }

    return (
        <span className={`checkbox--${props.name}`}>
            {props.boxes.map((box, key) => {
                const checked = props.value.indexOf(box.value) > -1
                const className = `form-check${props.inline ? ' form-check-inline' : ''}`
                return (
                    <div key={key} className={className}>
                        <Checkbox name={`${props.name}[]`}
                            value={box.value || ""}
                            label={box.label}
                            checked={checked || ""}
                            disabled={props.disabled}
                            onChange={() => toggle(box.value)} />
                    </div>
                )
            })}
        </span>
    )
}


CheckboxGroup.defaultProps = {
    boxes: [],
    value: []
}

CheckboxGroup.propTypes = {
    boxes: PropTypes.array,
    value: PropTypes.array
}

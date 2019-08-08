/* eslint-disable react/prop-types */
import React, { PropTypes } from 'react'

const noOp = () => {}

export const Checkbox = ({name, value, checked = false, onChange = noOp, attributes = {}, className = 'form-check-input'}) => {

    const onInput = () => {
        //ev.preventDefault()
        onChange(name, !checked)
    }

    if (checked) {
        attributes.checked = "checked"
    }

    return <input type="checkbox" {...{ name, value }} {...attributes} onChange={onInput} className={className} />
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
                <div className={className}>
                    <label className="form-check-label" {...{ key }}>
                        <Checkbox name={`${props.name}[]`}
                            value={box.value}
                            checked={checked}
                            onChange={() => toggle(box.value)} />
                    {box.label}
                    </label>
                </div>
            )
        })}
        </span>
    )
}


CheckboxGroup.defaultProps = {
    onChange: noOp,
    boxes: [],
    value: [],
    inline: false
}

CheckboxGroup.propTypes = {
    boxes: PropTypes.array,
    value: PropTypes.array
}

/* eslint-disable react/prop-types */
import React, { PropTypes } from 'react'

const noOp = () => {}

export const Checkbox = ({name, value, checked = false, onChange = noOp, attributes = {}}) => {
    console.log('Checkbox', name, value, checked)
    const onInput = () => {
        //ev.preventDefault()
        onChange(!checked)
    }

    if (checked) {
        attributes.checked = "checked"
    }

    return <input type="checkbox" {...{ name, value }} {...attributes} onChange={onInput} />
}

export const CheckboxGroup = (props) => {
    console.log('CheckboxGroup', props.value)

    const toggle = (toggleValue) => {
        const oldValue = props.value
        const newValue = (oldValue.indexOf(toggleValue) > -1)
                       ? oldValue.filter(v => v !== toggleValue)
                       : oldValue.concat([toggleValue])

        console.log('newValue', newValue)

        props.onChange(props.name, newValue)
    }

    return (
        <span>
        {props.boxes.map((box, key) => {
            const checked = props.value.indexOf(box.value) > -1
            const className = `checkbox-lalbel ${props.inline ? 'checkbox-inline' : ''}`

            return (
                <label {...{ key, className }}>
                    <Checkbox name={`${props.name}[]`}
                        value={box.value}
                        checked={checked}
                        onChange={() => toggle(box.value)} />
                {box.label}
                </label>
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

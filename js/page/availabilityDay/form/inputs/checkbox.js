/* eslint-disable react/prop-types */
import React, { Component, PropTypes } from 'react'

const noOp = () => {}

export const Checkbox = ({name, value, checked = false, onChange = noOp, attributes = {}}) => {
    const onInput = (ev) => {
        ev.preventDefault()
        onChange(!checked)
    }

    if (checked) {
        attributes.checked = true
    }

    return <input type="checkbox" {...{ name, value }} {...attributes} onChange={onInput} />
}

export class CheckboxGroup extends Component {
    toggle(name) {
        const boxes = this.props.boxes.map(box => {
            if (box.name === name) {
                const newBox = Object.assign({}, box)
                newBox.checked = !box.checekd
                return newBox
            } else {
                return box
            }
        })

        const values = boxes.filter(box => box.checked).map(box => box.value)
        this.props.onChange(values)
    }

    renderCheckbox(box, key) {
        const checked = this.props.value.indexOf(box.value) > -1

        const className = `checkbox-lalbel ${this.props.inline ? 'checkbox-inline' : ''}`

        return (
            <label {...{ key, className }}>
                <Checkbox name={box.name}
                    value={box.value}
                    checked={checked}
                    onChange={() => this.toggle(box.name)} />
                {box.label}
            </label>
        )
    }

    render() {
        return <span>
        {this.props.boxes.map(this.renderCheckbox.bind(this))}
        </span>
    }
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

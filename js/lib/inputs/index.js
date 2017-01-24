/* eslint-disable react/prop-types */
import React from 'react'
export * from './select'
export * from './checkbox'
export * from './time'
export * from './date'

const noOp = () => {}

export const Text = ({name, value, onChange = noOp, attributes = {}}) => {
    const widthClassName = attributes.width ? `input--size-${attributes.width}` : ''
    const className = `form-input ${attributes.className || ''} ${widthClassName}`

    const onInput = (ev) => onChange(name, ev.target.value)

    return <input type="text" onChange={onInput} {...{ name, value}} {...attributes} {...{ className }} />
}

export const Label = ({children}) => <label className="label">{children}</label>

export const FormGroup = (props) => {
    const className = `form-group ${props.error ? "has-error" : ""} ${props.className || ""}`
    return <div {...{ className }}>{props.children}</div>
}

export const Controls = ({ children }) => <div className="controls">{children}</div>

export const Description = ({ children }) => {
    return (<p className="form-input-description">{children}</p>)
}

/* eslint-disable react/prop-types */
import React from 'react'
export * from './select'
export * from './checkbox'
export * from './time'
export * from './date'

const noOp = () => { }

export const Text = ({ name, value, onChange = noOp, placeholder = "", width = "", attributes = {} }) => {
    const widthClassName = width ? `input--size-${width}` : ''
    const className = `form-control ${attributes.className || ''} ${widthClassName}`

    const onInput = (ev) => onChange(name, ev.target.value)

    return <input size={width} placeholder={placeholder} type="text" onChange={onInput} {...{ name, value }} {...attributes} {...{ className }} />
}

export const Hidden = ({ name, value, attributes = {} }) => {
    return <input type="hidden" {...{ name, value, attributes }} />
}

export const Textarea = ({ name, value, onChange = noOp, placeholder = "", width = "", attributes = {} }) => {
    const widthClassName = width ? `input--size-${width}` : ''
    const className = `form-control ${attributes.className || ''} ${widthClassName}`
    const onInput = (ev) => onChange(name, ev.target.value)

    return <textarea defaultValue={value} placeholder={placeholder} onChange={onInput} {...{ name }} {...attributes} {...{ className }}></textarea>
}

export const Label = ({ value, attributes = {}, children }) => <label {...attributes}>{value ? value : children}</label>

export const FormGroup = (props) => {
    const className = `form-group${props.inline ? " form-group--inline" : ""}${props.error ? " has-error" : ""} ${props.className || ""}`
    return <div {...{ className }}>{props.children}</div>
}

export const Controls = ({ children }) => <div className="controls">{children}</div>

export const Description = ({ value, attributes = {}, children }) => {
    return (<small className="formgroup__help" {...attributes}>{value ? value : children}</small>)
}

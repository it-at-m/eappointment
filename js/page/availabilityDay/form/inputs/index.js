/* eslint-disable react/prop-types */
import React from 'react'
export * from './select'
export * from './checkbox'

const noOp = () => {}

export const Text = ({name, value, onChange = noOp, attributes = {}}) => {
    console.log("text", attributes)
    return <input type="text" {...{ name, value, onChange}} {...attributes} />
}

export const Date = ({name, value, onChange = noOp, attributes = {}}) => {
    return (
        <div className="add-date-picker">
            <input type="text" {...{ name, value, onChange}} {...attributes} />
        </div>
    )
}

export const Label = ({children}) => <label className="label">{children}</label>

export const FormGroup = (props) => {
    const className = `form-group ${props.error ? "has-error" : ""} ${props.className || ""}`
    return <div {...{ className }}>{props.children}</div>
}

export const Controls = ({ children }) => <div className="controls">{children}</div>

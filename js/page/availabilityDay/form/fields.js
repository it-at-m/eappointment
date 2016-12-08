/* eslint-disable react/prop-types */
import React from 'react'

const argsToAttributes = (args, onChange) => {
    const { name,
            value,
            size,
            maxlength,
            readonly,
            placeholder,
            accesskey,
            id,
            className = '',
            disabled,
            checked,
            multiple } = args

    const attributes = {
        name,
        value,
        size,
        placeholder,
        maxlength,
        accesskey,
        id,
        className,
        onChange
    }

    if (readonly) {
        attributes.readonly = true
    }

    if (disabled) {
        attributes.disabled = false
    }

    if (multiple) {
        attributes.multiple = true
    }

    if (checked) {
        attributes.multiple = true
    }

    return attributes
}


export const Input = ({args, value, onChange}) => {
    const { width, type = 'text' } = args
    const attributes = argsToAttributes(args, onChange)
    const widthClass = width ? `input--size-${width}` : ""
    const className = `${attributes.className} ${widthClass}`

    return (
        <input {...attributes} {...{ className, type, value }}/>
    )
}

export const Submit = ({args}) => {
    const {type = 'submit'} = args
    const className = `button-${type}`
    return <input name={args.name} value={args.value} {...{ type, className }} />
}

export const DatePicker = ({args, value, onChange}) => {
    const { width, type = 'text'} = args
    const attributes = argsToAttributes(args, onChange)
    const widthClass = width ? `input--size-${width}` : ""
    const className = `${attributes.className} ${widthClass}`
    return (
        <div className="add-date-picker">
            <input {...attributes} {...{ className, type, value }}/>
        </div>
    )
}

const Option = opt => {
    const valueSelected = opt.value === opt.selectedValue
    const attributes = {}
    if (opt.selected || valueSelected) {
        attributes.selected = true
    }

    return (
        <option title={opt.name} value={opt.value} {...attributes}>{opt.name}</option>
    )
}

const OptGroup = item => {
    const { selectedValue } = item

    if (item.options && item.options.length > 0) {
        return (
            <optgroup label={item.name}>
                {item.options.map(opt => <Option {...opt} {... { selectedValue }} />)}
            </optgroup>
        )
    } else {
        return <Option {...item} />
    }
}

export const Select = ({args, value, onChange}) => {
    const attributes = argsToAttributes(args, onChange)
    return (
        <select {...attributes} >
            {args.options.map(item => <OptGroup {...item} selectedValue={value} />)}
        </select>
    )
}

export const Checkbox = ({args, value, onChange}) => {
    const { type = 'checkbox', inline } = args
    const attributes = argsToAttributes(args, onChange)

    if (attributes.value === value) {
        attributes.checked = true
    }

    const labelClassName = `checkbox-label ${inline ? 'checkbox-inline' : ''}`

    return (
        <label className={labelClassName}>
            <input {...attributes} {...{ type }} />
            {args.label}
            {args.required ? <span className="required-symbol">*</span> : null}
            {" "}
        </label>
    )
}

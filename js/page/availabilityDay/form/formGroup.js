import React, { PropTypes } from 'react'

import * as Fields from './fields'

const renderLabel = (groupArgs) => {
    if (groupArgs.label ) {
        return <label className="label">
        {groupArgs.label}
        {groupArgs.required ? <span className="required-symbol">*</span> :null}
        </label>
    }

    if (groupArgs.divlabel) {
        return <div className="label">
        {groupArgs.label}
        {groupArgs.required ? <span className="required-symbol">*</span> :null}
        </div>
    }
}

const getElementRenderType = element => {
    switch (element.type) {
        case 'input': return Fields.Input
        case 'select': return Fields.Select
        case 'checkbox': return Fields.Checkbox
        case 'datepicker': return Fields.DatePicker
    }
}

const renderElement = (element, data, onChange) => {
    const RenderType = getElementRenderType(element)

    const onElementChange = ev => {
        const changedName = element.parameter.name
        const changedValue = ev.target.value

        onChange(changedName, changedValue)
    }

    const name = element.parameter.name
    const value = data[name]

    console.log('renderElement', name, element, value)

    return <RenderType args={element.parameter} onChange={onElementChange} {...{ value }} />
}

const renderPrepend = element => {
    if (element.parameter.prepend) {
        return <span className="prepend"> {element.parameter.prepend} </span>
    }
}

const renderAppend = element => {
    if (element.parameter.append) {
        return <span className="append"> {element.parameter.append} </span>
    }
}

const FormGroup = ({ groupArgs, elements = [], data, onChange }) => {
    console.log('FormGroup groupArgs', groupArgs)
    const hasError = groupArgs.errors && groupArgs.errors.length > 0 ? 'has-error' : ''
    const className = `form-group ${hasError} ${groupArgs.className || ''}`
    return (
        <div {...{ className }}>
            {renderLabel(groupArgs)}
            <div className="controls">
                {elements.map(element => <span>
                    {renderPrepend(element)}
                    {renderElement(element, data, onChange)}
                    {renderAppend(element)}
                </span>)}
            </div>
        </div>
    )
}

FormGroup.propTypes = {
    groupArgs: PropTypes.object.isRequired,
    elements: PropTypes.array,
    onChange: PropTypes.func.isRequired,
    data: PropTypes.object.isRequired
}

export default FormGroup

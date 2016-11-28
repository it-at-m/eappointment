import { render } from 'react-dom'
import React from 'react'

const attributesToArray = attributes => Array.prototype.slice.call(attributes, 0)

const tryJson = (input) => {
    try {
        return JSON.parse(input)
    } catch (e) {
        return input
    }
}

const getDataAttributes = (element) => {
    const attributes = attributesToArray(element.attributes)
    const dataRegex = /^data-/i 

    return attributes
        .filter(attribute => (dataRegex.test(attribute.nodeName)))
        .map(attribute => [
            attribute.name.replace(dataRegex, ''),
            attribute.value]
        )
        .reduce((carry, [key, value]) => {
            carry[key] = tryJson(value)
            return carry
        }, {})
}

export default (selector, Component) => {
    const elements = document.querySelectorAll(selector)

    elements.forEach(element => {
        const props = getDataAttributes(element)

        render(<Component {...props} />, element)
    })
}

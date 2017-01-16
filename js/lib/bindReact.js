import { render } from 'react-dom'
import React from 'react'
import { getDataAttributes } from '../lib/utils'

export default (selector, Component) => {
    const elements = Array.prototype.slice.call(document.querySelectorAll(selector), 0)

    if (elements) {
        elements.forEach(element => {
            const props = getDataAttributes(element)

            render(<Component {...props} />, element)
        })
    }
}

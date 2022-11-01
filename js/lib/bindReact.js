import {createRoot} from 'react-dom/client';
import React from 'react'
import { getDataAttributes } from '../lib/utils'

export default (selector, Component) => {
    const elements = Array.prototype.slice.call(document.querySelectorAll(selector), 0)

    if (elements) {
        elements.forEach(element => {
            const root = createRoot(element);
            const props = getDataAttributes(element)

            root.render(<Component {...props} />)
        })
    }
}

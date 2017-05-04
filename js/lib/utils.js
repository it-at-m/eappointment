import $ from 'jquery'
import moment from 'moment'

export const timeToFloat = (time) => {
    const momentTime = moment(time, 'HH:mm:ss')

    return momentTime.hours() + (momentTime.minutes() / 60)
}

export const timestampToFloat = timestamp => {
    const momentTime = moment(timestamp, 'X')

    return momentTime.hours() + (momentTime.minutes() / 60)
}

export const range = (start, end, step = 1) => {
    const result = []
    for (let i = start; i <= end; i += step) {
        result.push(i)
    }

    return result
}

export const deepGet = (obj, path = []) => path.reduce((carry, current) => carry ? carry[current] : undefined, obj)

const attributesToArray = attributes => Array.prototype.slice.call(attributes, 0)

export const getDataAttributes = (element) => {
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

export const tryJson = (input) => {
    try {
        return JSON.parse(input)
    } catch (e) {
        return input
    }
}

const lightboxHtml = '<div class="lightbox"><div class="lightbox__content"></div></div>'

export const lightbox = (parentElement, onBackgroundClick) => {
    const lightboxElement = $(lightboxHtml)

    if (!parentElement) {
        parentElement = $('body')
        lightboxElement.addClass('fixed')
    }

    const destroyLightbox = () => {
        lightboxElement.off()
        lightboxElement.remove()
    }

    const lightboxContentElement = lightboxElement.find('.lightbox__content');

    lightboxElement.on('click', (ev) => {
        console.log('background click', ev);
        ev.stopPropagation()
        ev.preventDefault()
        destroyLightbox()
        onBackgroundClick()
    }).on('click', '.lightbox__content', (ev) => {
        ev.stopPropagation();
    })

    $(parentElement).append(lightboxElement)

    return {
        lightboxContentElement,
        destroyLightbox
    }
}

export const noOp = () => {}

export const getUrlParameters = () => {
    return document.location.search.replace(/^\?/, "")
                   .split("&")
                   .reduce((carry, current) => {
                       const [key, value] = current.split('=')

                       if (key) {
                           return Object.assign({}, carry, {[key]: value})
                       } else {
                           return carry
                       }
                   }, {})
}

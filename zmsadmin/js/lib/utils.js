
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

export const loopWithCallback = (array, callback) => {
    for (var i = 0; i < array.length; i++) {
        let result = callback(array[i])
        if (result) { return result }
    }
}

export const deepGet = (obj, path = []) => path.reduce((carry, current) => carry ? carry[current] : undefined, obj)

export const deepMerge = (target, ...sources) => {
    if (!sources.length) return target;
    target = toObject(target)
    const source = toObject(sources.shift())

    for (const key in source) {
        if (isObject(source[key])) {
            if (!target[key]) Object.assign(target, { [key]: {} });
            deepMerge(target[key], source[key]);
        } else {
            Object.assign(target, { [key]: source[key] });
        }
    }


    return deepMerge(target, ...sources);
}

export const isObject = (item) => {
    return (item && typeof item === 'object' && !Array.isArray(item));
}

export const toObject = (arr) => {
    let newObj = arr;
    if (!isObject(newObj)) {
        newObj = Object.assign({}, arr)
    }
    return newObj;
}

export const getFieldList = (field) => {
    let fieldList = [];
    let match;
    let reg = RegExp('([^[]+)', 'g');
    while ((match = reg.exec(field))) {
        fieldList.push(match.pop().replace(/\]/g, ''))
    }
    return fieldList;
}

export const makeNestedObj = (arr, value) => {
    const reducer = (acc, item) => ({ [item]: acc });
    return arr.reduceRight(reducer, value);
}

const attributesToArray = attributes => Array.prototype.slice.call(attributes, 0)

export const toArray = (data) => {
    return Object.keys(data).map(key => ({ [key]: data[key] }));
}

export const inArray = (value,arr) => {
    var status = false;
    for(var i=0; i<arr.length; i++){
      var name = arr[i];
      if(name == value){
        status = true;
        break;
      }
    }
    return status;
  }

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

const lightboxHtml = '<div class="lightbox"><div class="lightbox__content" role="dialog" aria-modal="true"></div></div>'

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
        //console.log('background click', ev);
        ev.stopPropagation()
        ev.preventDefault()
        destroyLightbox()
        onBackgroundClick()
    }).on('click', '.lightbox__content', (ev) => {
        ev.stopPropagation();
    })


    if ($(parentElement).find('.lightbox').length) {
        $(parentElement).find('.lightbox').remove();
    }
    $(parentElement).append(lightboxElement)

    return {
        lightboxContentElement,
        destroyLightbox
    }
}

export const noOp = () => { }

export const stopEvent = (ev) => {
    if (ev) {
        ev.preventDefault();
        ev.stopPropagation();
    }
}

const unsafeQueryParamKey = (key) =>
    key === '__proto__' || key === 'constructor' || key === 'prototype'

export const getUrlParameters = () => {
    const pairs = []
    document.location.search.replace(/^\?/, "")
        .split("&")
        .forEach((current) => {
            const [key, value] = current.split('=')
            if (key && !unsafeQueryParamKey(key)) {
                pairs.push([key, value])
            }
        })
    return Object.fromEntries(pairs)
}

export const showSpinner = ($container = null) => {
    var loaderContainer = $('#main-content');
    if ($container !== null) {
        loaderContainer = $container.find('.body').first();
    }
    loaderContainer.prepend('<div class="loader" aria-hidden="true"><div class="spinner"></div></div>');
}

export const hideSpinner = ($container = null) => {
    var loaderContainer = $('#main-content');
    if ($container !== null) {
        loaderContainer = $container.find('.body').first();
        loaderContainer.find('.loader').detach();
    } else {
        loaderContainer.find('.loader').first().detach();
    }

}

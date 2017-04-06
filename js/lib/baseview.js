import $ from "jquery";
import ErrorHandler from './errorHandler';
import ExceptionHandler from './exceptionHandler';
import { lightbox } from './utils';
import { noOp } from './utils'

const loaderHtml = '<div class="loader"></div>'
const loaderSmallHtml = '<div class="loader-small"></div>'

class BaseView extends ErrorHandler {

    constructor(element, options = {}) {
        super();
        this.$main = $(element);
        this.$main.off();
        this.options = options;
        this.loadPromise = Promise.reject(null).catch(noOp);
    }

    loadContent(url, method = 'GET', data = null, loader = null) {
        let loaderContainer = this.$main.find('.body')

        if (loaderContainer.length < 1) {
            loaderContainer = this.$main;
        }

        if (loader && loader == 'small') {
            loaderContainer.html(loaderSmallHtml);
        } else {
            loaderContainer.html(loaderHtml);
        }


        const ajaxSettings = {
            method
        };

        if (method === 'POST' || method === 'PUT') {
            ajaxSettings.data = data;
        }

        this.loadPromise = new Promise((resolve, reject) => {
            $.ajax(url, ajaxSettings).done(responseData => {
                this.$main.html(responseData);
                resolve(this.$main);
            }).fail(err => {
                let isException = $(err.responseText).filter('.exception').length > 0;
                if (err.status >= 400 && err.status < 500 && isException) {
                    const { lightboxContentElement, destroyLightbox } = lightbox(null, () => {
                        reject({'source': 'lightbox', 'message': err.responseText})
                    })

                    const exceptionHandler = new ExceptionHandler(lightboxContentElement, {
                        code: err.status,
                        message: err.responseText,
                        callback: (exceptionButtonUrl) => {
                            destroyLightbox()
                            reject({'source': 'button', 'url': exceptionButtonUrl })
                        }
                    })

                } else {
                    console.log('XHR load error', url, err);
                    reject(err);
                }
            })
        })

        return this.loadPromise;
    }

    loadCall(url, method = 'GET', data = null) {
        const ajaxSettings = {
            method
        };
        if (method === 'POST' || method === 'PUT') {
            ajaxSettings.data = data;
        }
        return new Promise((resolve, reject) => {
            $.ajax(url, ajaxSettings).done(responseData => {
                resolve(responseData);
            }).fail(err => {
                let isException = err.responseText.toLowerCase().includes('exception');
                if (err.status >= 400 && err.status < 500 && isException) {
                    const { lightboxContentElement, destroyLightbox } = lightbox(null, () => {
                        reject({'source': 'lightbox', 'message': err.responseText})
                    })

                    const exceptionHandler = new ExceptionHandler(lightboxContentElement, {
                        code: err.status,
                        message: err.responseText,
                        callback: (exceptionButtonUrl) => {
                            destroyLightbox()
                            reject({'source': 'button', 'url': exceptionButtonUrl})
                        }
                    })

                } else {
                    console.log('XHR load error', url, err);
                    reject(err);
                }
            })
        })
    }

    destroy() {
        this.$main.off().empty();
        this.$main = null;
    }

    get $ () {
        return this.$main;
    }

}

export default BaseView;

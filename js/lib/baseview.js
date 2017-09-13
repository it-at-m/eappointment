import window from "window"
import $ from "jquery";
import ErrorHandler from './errorHandler';
import ExceptionHandler from './exceptionHandler';
import { lightbox } from './utils';
import { noOp } from './utils'

const loaderHtml = '<div class="loader"><div class="spinner"></div></div>'

class BaseView extends ErrorHandler {

    constructor(element, options = {}) {
        super();
        this.$main = $(element);
        this.$main.off();
        this.options = options;
        this.loadPromise = Promise.reject(null).catch(noOp);
    }

    showSpinner(container = null)
    {
        if (container !== null) {
            var loaderContainer = container.find('.body').first();
            if (loaderContainer.length < 1)
                loaderContainer = container;
        } else {
            var loaderContainer = this.$main.find('.body').first();
            if (loaderContainer.length < 1)
                loaderContainer = this.$main;
        }


        loaderContainer.prepend(loaderHtml);
    }

    loadContent(url, method = 'GET', data = null, container = null, spinner = true) {
        if (spinner) {
            this.showSpinner(container);
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
                if (err.responseText) {
                    let isException = err.responseText.toLowerCase().includes('exception');
                }
                if (err.status >= 400 && isException) {
                    const { lightboxContentElement, destroyLightbox } = lightbox(null, () => {
                        reject({'source': 'lightbox', 'message': err.responseText})
                    })

                    const exceptionHandler = new ExceptionHandler(lightboxContentElement, {
                        code: err.status,
                        message: err.responseText,
                        callback: (exceptionButtonUrl) => {
                            destroyLightbox()
                            if (err.responseText.includes('data-reload')) {
                                window.location.href = exceptionButtonUrl;
                            }
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
                if (err.responseText) {
                    let isException = err.responseText.toLowerCase().includes('exception');
                }
                if (err.status >= 400 && isException) {
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

    cleanReload () {
        window.location.href = window.location.href;
    }

}

export default BaseView;

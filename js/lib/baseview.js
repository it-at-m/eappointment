import window from "window"
import $ from "jquery";
import ErrorHandler from './errorHandler';
import ExceptionHandler from './exceptionHandler';
import MessageHandler from './messageHandler';
import DialogHandler from './dialogHandler';
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
        var loaderContainer = this.$main.find('.body').first();
        if (container !== null) {
            if (loaderContainer.length < 1)
                loaderContainer = container;
        } else {
            if (loaderContainer.length < 1)
                loaderContainer = this.$main;
        }


        loaderContainer.prepend(loaderHtml);
    }

    hideSpinner(container = null)
    {
        var loaderContainer = this.$main.find('.body').first();
        if (container !== null) {
            if (loaderContainer.length < 1)
                loaderContainer = container;
        } else {
            if (loaderContainer.length < 1)
                loaderContainer = this.$main;
        }
        loaderContainer.find('.loader').detach();
    }

    loadContent(url, method = 'GET', data = null, container = null, spinner = true) {
        if (container !== null) {
            this.$main = container;
        }

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
                resolve(responseData);
            }).fail(err => {
                let isException = err.responseText.toLowerCase().includes('exception');
                if (err.status >= 400 && isException) {
                    new ExceptionHandler(this.$main, {
                        code: err.status,
                        message: err.responseText
                    });
                    this.hideSpinner();
                } else {
                    console.log('XHR load error', url, err);
                    reject(err);
                }
            })
        });
        DialogHandler.hideMessages();
        return this.loadPromise;
    }

    loadCall(url, method = 'GET', data = null, spinner = false) {
        if (spinner) {
            this.showSpinner();
        }
        const ajaxSettings = {
            method
        };
        if (method === 'POST' || method === 'PUT') {
            ajaxSettings.data = data;
        }
        DialogHandler.hideMessages();
        return new Promise((resolve, reject) => {
            $.ajax(url, ajaxSettings).done(responseData => {
                resolve(responseData);
            }).fail(err => {
                let isException = err.responseText.toLowerCase().includes('exception');
                if (err.status >= 400 && isException) {
                    new ExceptionHandler(this.$main, {
                        code: err.status,
                        message: err.responseText
                    });
                    this.hideSpinner();
                } else {
                    console.log('XHR load error', url, err);
                    reject(err);
                }
            })
        });
    }

    destroy() {
        this.$main.off().empty();
        this.$main = null;
    }

    get $ () {
        return this.$main;
    }

    cleanReload () {
        window.setTimeout(() => {
            console.log("Clean reload %o", window.location.href);
            window.location.assign(window.location.href)
        }, 400);
    }

    locationLoad (url) {
        window.location.href = url;
    }

    loadMessage (response, callback) {
        this.$main.find('.form-actions').hide();
        const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {callback()})
        new MessageHandler(lightboxContentElement, {
            message: response,
            callback: () => {
                callback();
                destroyLightbox();
            }
        })
    }

    loadDialog (response, callback) {
        const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {callback()})
        new DialogHandler(lightboxContentElement, {
            response: response,
            callback: (message) => {
                if (message) {
                    if ($(message).find('.dialog form').length > 0) {
                        this.loadDialog(message, callback);
                    }
                    else {
                        this.loadMessage(message, callback);
                    }
                }
                destroyLightbox();
            },
            loader: this.loadCall
        })
    }

    loadErrorCallback(err) {
        if (err.message.toLowerCase().includes('exception')) {
            let exceptionType = $(err.message).filter('.exception').data('exception');
            this.load();
            console.log('EXCEPTION thrown: ' + exceptionType);
        }
        else
            console.log('Ajax error', err);
    }
}

export default BaseView;

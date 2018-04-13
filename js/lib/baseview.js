import window from "window"
import $ from "jquery";
import ErrorHandler from './errorHandler';
import ExceptionHandler from './exceptionHandler';
import MessageHandler from './messageHandler';
import DialogHandler from './dialogHandler';
import { lightbox, showSpinner, hideSpinner } from './utils';
import { noOp } from './utils'

class BaseView extends ErrorHandler {

    constructor(element, options = {}) {
        super();
        this.$main = $(element);
        this.$main.off();
        this.options = options;
        this.loadPromise = Promise.reject(null).catch(noOp);
    }

    loadContent(url, method = 'GET', data = null, container = null, spinner = true) {
        if (container !== null) {
            this.$main = container;
        }

        if (spinner) {
            showSpinner(this.$main);
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
                DialogHandler.hideMessages();
                resolve(responseData);
            }).fail(err => {
                let isException = err.responseText.toLowerCase().includes('exception');
                if (err.status >= 400 && isException) {
                    new ExceptionHandler(this.$main, {
                        code: err.status,
                        message: err.responseText,
                        parent: this
                    });
                    hideSpinner(this.$main);
                } else {
                    console.log('XHR load error', url, err);
                    reject(err);
                }
            })
        });
        return this.loadPromise;
    }

    loadCall(url, method = 'GET', data = null, spinner = false) {
        return BaseView.loadCallStatic(url, method, data, spinner, this);
    }

    static loadCallStatic(url, method = 'GET', data = null, spinner = false, parent) {
        if (spinner) {
            showSpinner(parent.$main);
        }
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
                if (err.status >= 400 && isException) {
                    new ExceptionHandler(parent.$main, {
                        code: err.status,
                        message: err.responseText,
                        parent: parent
                    });
                    hideSpinner(parent.$main);
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
        const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {
            destroyLightbox();
            callback();
        });
        new MessageHandler(lightboxContentElement, {
            message: response,
            callback: () => {
                callback();
                destroyLightbox();
            },
            handleLightbox: destroyLightbox
        })
    }

    loadDialog(response, callback) {
        BaseView.loadDialogStatic(response, callback, this);
    }

    static loadDialogStatic (response, callback, parent) {
        const { lightboxContentElement, destroyLightbox } = lightbox(parent.$main, () => {
            destroyLightbox(),
            callback()
        });
        new DialogHandler(lightboxContentElement, {
            response: response,
            callback: () => {
                callback();
                destroyLightbox();
            },
            parent: parent,
            loader: parent.loadCall,
            handleLightbox: destroyLightbox
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

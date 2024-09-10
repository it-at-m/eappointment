import $ from "jquery";
import ErrorHandler from './errorHandler';
import ExceptionHandler from './exceptionHandler';
import MessageHandler from './messageHandler';
import DialogHandler from './dialogHandler';
import { lightbox, showSpinner, hideSpinner, noOp } from './utils';

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

        return new Promise((resolve, reject) => {
            $.ajax(url, ajaxSettings).done(responseData => {
                this.$main.html(responseData);
                DialogHandler.hideMessages();
                resolve(responseData);
            }).fail((err) => {
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
    }

    loadCall(url, method = 'GET', data = null, spinner = false, $container = this.$main) {
        return BaseView.loadCallStatic(url, method, data, spinner, $container);
    }

    static loadCallStatic(url, method = 'GET', data = null, spinner = false, parent) {
        if (parent !== null) {
            this.$main = parent;
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
        return new Promise((resolve, reject) => {
            $.ajax(url, ajaxSettings).done(responseData => {
                resolve(responseData);
            }).fail(err => {
                let isException = err.responseText.toLowerCase().includes('exception');
                if (err.status >= 400 && isException) {
                    new ExceptionHandler(this.$main, {
                        code: err.status,
                        message: err.responseText,
                        parent: this.$main
                    });
                    hideSpinner(this.$main);
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

    get $() {
        return this.$main;
    }

    cleanReload() {
        window.setTimeout(() => {
            console.log("Clean reload %o", window.location.href);
            window.location.assign(window.location.href)
        }, 400);
    }

    locationLoad(url) {
        window.location.href = url;
    }

    loadMessage(response, callback, $container = null, returnTarget = null) {
        if (!$container) {
            $container = this.$main;
        }
        
        //$container.find('.form-actions').hide();
        const { lightboxContentElement, destroyLightbox } = lightbox($container, () => {
            destroyLightbox();
            returnTarget.focus();
            callback();
        });

        new MessageHandler(lightboxContentElement, {
            message: response,
            callback: () => {
                callback();
                destroyLightbox();
                returnTarget.focus();
            },
            parent: this,
            handleLightbox: destroyLightbox
        })

        const dialog = document.getElementsByClassName('dialog')[0]
        dialog.focus();
    }

    loadDialog(response, callback, abortCallback, returnTarget) {
        BaseView.loadDialogStatic(response, callback, abortCallback, this, false, returnTarget);
    }

    static loadDialogStatic(response, callback, abortCallback, parent, callbackAsBackgroundAction = false, returnTarget = false) {
        var $container = null;
        var $loader = null;
        if (parent) {
            $container = parent.$main;
            $loader = parent.loadCall;
        }

        const { lightboxContentElement, destroyLightbox } = lightbox($container, () => {
            destroyLightbox();
            (callbackAsBackgroundAction) ? callback() : (abortCallback) ? abortCallback() : () => { }
        });
        new DialogHandler(lightboxContentElement, {
            response: response,
            callback: () => {
                callback();
                destroyLightbox();
                returnTarget && returnTarget.focus();
            },
            abortCallback: () => {
                (abortCallback) ? abortCallback() : () => { }
                destroyLightbox();
                returnTarget && returnTarget.focus();
            },
            parent: parent,
            returnTarget: returnTarget,
            loader: $loader
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

import $ from "jquery";
import BindHandler from "./bindHandler";
import ExceptionHandler from './exceptionHandler';
import DialogHandler from './dialogHandler';
import { lightbox, showSpinner, hideSpinner, noOp } from './utils';

class BaseView extends BindHandler {

    constructor(element, options = {}) {
        super();
        this.$main = $(element);
        this.$main.off();
        this.options = options;
        this.loadPromise = Promise.reject(null).catch(noOp);
    }

    get $() {
        return this.$main;
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

    static loadDialogStatic(response, callback, parent, callbackAsBackgroundAction = false) {
        var $container = null;
        var $loader = null;
        if (parent) {
            $container = parent.$main;
            $loader = parent.loadCall;
        }

        const { lightboxContentElement, destroyLightbox } = lightbox($container, () => {
            destroyLightbox(),
                (callbackAsBackgroundAction) ? callback() : () => { }
        });
        new DialogHandler(lightboxContentElement, {
            response: response,
            callback: () => {
                callback();
                destroyLightbox();
            },
            parent: parent,
            loader: $loader,
            handleLightbox: destroyLightbox
        })
    }
}

export default BaseView;

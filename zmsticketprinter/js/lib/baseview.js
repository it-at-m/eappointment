import $ from "jquery";
import ErrorHandler from './errorHandler';
import DialogHandler from './dialogHandler';
import { lightbox } from './utils';

class BaseView extends ErrorHandler {

    constructor(element) {
        super(element);
        this.$main = $(element);
    }

    get $ () {
        return this.$main;
    }

    static loadCallStatic(url, method = 'GET', data = null) {
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
                console.log('XHR load error', url, err);
                reject(err);
            })
        });
    }

    static loadDialogStatic (response, callback) {
        const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {
            destroyLightbox(),
            callback()
        });
        new DialogHandler(lightboxContentElement, {
            response: response,
            callback: () => {
                callback();
                destroyLightbox();
            }
        })
    }

}

export default BaseView;

import $ from "jquery";
import ErrorHandler from './errorHandler';

import { noOp } from './utils'

const loaderHtml = '<div class="loader"></div>'

class BaseView extends ErrorHandler {

    constructor(element, options = {}) {
        super();
        this.$main = $(element);
        this.$main.off();
        this.options = options;
        this.loadPromise = Promise.reject(null).catch(noOp);
    }

    loadContent(url, method = 'GET', data = null) {
        let loaderContainer = this.$main.find('.body')

        if (loaderContainer.length < 1) {
            loaderContainer = this.$main;
        }

        loaderContainer.html(loaderHtml);

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
                if (err.status > 400) {
                    this.$main.html($(err.responseText));
                    resolve(this.$main);
                } else {
                    console.log('XHR load error', url, err);
                    reject(err);
                }
            })
        })

        return this.loadPromise;
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

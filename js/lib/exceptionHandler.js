import $ from 'jquery';
import BaseView from './baseview'
import { lightbox } from './utils';

class ExceptionHandler {

    constructor (element, options) {
        this.$main = $(element)
        this.message = options.message;
        this.code = options.code;
        this.callback = options.callback;
        this.bindEvents();
        this.render();
        console.log(this);
    }

    render() {
        this.$main.html($(this.message).filter('.exception'));
    }

    bindEvents() {
        this.$main.off().on('click', '.btn', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.callback($(ev.target).attr('href'));
        })
    }
}

export default ExceptionHandler

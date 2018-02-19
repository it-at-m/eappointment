import $ from 'jquery';
import ExceptionHandler from './exceptionHandler'
import maxChars from '../element/form/maxChars'

class DialogHandler {

    constructor (element, options) {
        this.$main = $(element);
        this.response = options.response;
        this.callback = options.callback || (() => {});
        this.loader = options.loader || (() => {});
        this.handleLightbox = options.handleLightbox || (() => {});
        this.bindEvents();
        this.render();
    }

    render() {
        var content = $(this.response).filter('div.dialog');
        if (content.length == 0) {
            var message = $(this.response).find('div.dialog');
            if (message.length > 0) {
                content = message.get(0).outerHTML;
            }
        }
        if (content.length == 0) {
            new ExceptionHandler(this.$main, {'message': this.response});
        } else {
            this.$main.html(content);
        }

        $('textarea.maxchars').each(function() {
            maxChars(this);
        })
    }

    bindEvents() {
        this.$main.off().on('click', '.button-ok', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.callback();
        }).on('click', '.button-abort', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.handleLightbox();
        });
    }

    static hideMessages()
    {
        let message = $.find('.message');
        if (message.length) {
            setTimeout(() => {
                $(message).not('.message-keep').fadeOut().remove();
            },5000)
        }
    }
}

export default DialogHandler

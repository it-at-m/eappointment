import $ from 'jquery';
import ExceptionHandler from './exceptionHandler'
import settings from '../settings' 

class DialogHandler {

    constructor (element, options) {
        this.$main = $(element);
        this.response = options.response;
        this.callback = options.callback || (() => {});
        this.parent = options.parent;
        this.loader = options.loader || (() => {});
        this.handleLightbox = options.handleLightbox || (() => {});
        this.bindEvents();
        this.render();
    }

    render() {
        DialogHandler.hideMessages(true);
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
    }

    bindEvents() {
        this.$main.off().on('click', '.button-ok', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.callback(ev);
        }).on('click', '.button-abort', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.handleLightbox();
        }).on('click', '.button-callback', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            var callback = $(ev.target).data('callback');
            this.callback = this.parent[callback];
            this.callback(ev);
        });
    }

    static hideMessages(instant = false)
    {
        let message = $.find('.message, .dialog');
        if (message.length && ! instant) {
            setTimeout(() => {
                $(message).not('.message-keep').fadeOut().remove();
            }, settings.hideMessageTime * 1000)
        } else if (message.length && instant) {
            $(message).not('.message-keep').fadeOut().remove();
        }
    }
}

export default DialogHandler

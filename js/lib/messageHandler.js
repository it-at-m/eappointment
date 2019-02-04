import $ from 'jquery';
import ExceptionHandler from './exceptionHandler'
import DialogHandler from './dialogHandler'

class MessageHandler {

    constructor(element, options) {
        this.$main = $(element)
        this.message = options.message;
        this.parent = options.parent;
        this.callback = options.callback || (() => { });
        this.handleLightbox = options.handleLightbox || (() => { });
        this.bindEvents();
        this.render()
    }

    render() {
        var content = $(this.message).filter('div.dialog');
        if (content.length == 0) {
            var message = $(this.message).find('div.dialog');
            if (message.length > 0) {
                content = message;
            }
        }
        if (content.length == 0) {
            new ExceptionHandler(this.$main, { 'message': this.message, 'callback': this.callback });
        } else {
            DialogHandler.hideMessages(true);
            if ($(this.$main.get(0)).hasClass('lightbox__content')) {
                this.$main.html(content.get(0).outerHTML);
            } else {
                this.$main.find('.body').prepend(content.get(0).outerHTML);
            }
        }
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
        }).on('click', '.button-callback', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            var callback = $(ev.target).data('callback');
            this.parent[callback](ev);
            this.callback();
            this.handleLightbox();
        });
    }
}

export default MessageHandler

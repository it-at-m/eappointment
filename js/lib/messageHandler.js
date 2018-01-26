import $ from 'jquery';
import ExceptionHandler from './exceptionHandler'

class MessageHandler {

    constructor (element, options) {
        this.$main = $(element)
        this.message = options.message;
        this.callback = options.callback || (() => {});
        this.bindEvents();
        this.render();
        console.log(this);
    }

    render() {
        var content = $(this.message).filter('div.dialog');
        if (content.length == 0) {
            var message = $(this.message).find('div.dialog');
            if (message.length > 0) {
                content = message.get(0).outerHTML;
            }
        }
        if (content.length == 0) {
            new ExceptionHandler(this.$main, {'message': this.message, 'callback': this.callback});
        } else {
            this.$main.find('.body').prepend(content);
        }
    }

    bindEvents() {
        this.$main.off().on('click', '.btn', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.callback($(ev.target).data('action'), $(ev.target).attr('href'), ev);
        });
    }
}

export default MessageHandler

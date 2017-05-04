import $ from 'jquery';
import BaseView from './baseview'
import { lightbox } from './utils';

class MessageHandler {

    constructor (element, options) {
        this.$main = $(element)
        this.message = options.message;
        this.callback = options.callback;
        this.bindEvents();
        this.render();
        console.log(this);
    }

    render() {
        var message = $(this.message).filter('div.dialog');
        if (message.length == 0) {
            var message = $(this.message).find('div.dialog').get(0).outerHTML;
        }
        this.$main.html(message);
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

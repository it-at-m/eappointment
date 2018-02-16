import $ from 'jquery';
import ExceptionHandler from './exceptionHandler'
import maxChars from '../element/form/maxChars'

class DialogHandler {

    constructor (element, options) {
        this.$main = $(element);
        this.response = options.response;
        this.callback = options.callback || (() => {});
        this.nextCall = options.nextCall || (() => {});
        this.loader = options.loader || (() => {});
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
        this.$main.off().on('click', '.button-submit', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const sendData = this.$main.find('form').serializeArray();
            sendData.push(
                {'name': 'submit', 'value':'form'},
                {'name': 'dialog', 'value':1}
            );
            const url = this.$main.find('form').attr('action');
            this.loader(url, 'POST', $.param(sendData)).then((response) => this.callback(response));
        }).on('click', '.button-cancel', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.callback()
        }).on('click', '.button-call', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.nextCall()
        });;
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

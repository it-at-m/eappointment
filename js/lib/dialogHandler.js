import $ from 'jquery';
import BaseView from './baseview'
import { lightbox } from './utils';
import maxChars from '../element/form/maxChars'

class DialogHandler extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.$main = $(element)
        this.response = options.response;
        this.callback = options.callback;
        this.bindEvents();
        this.render();
    }
    render() {
        var content = $(this.response).filter('div.dialog');
        if (content.length == 0) {
            var content = $(this.response).find('div.dialog').get(0).outerHTML;
        }
        this.$main.html(content);
        $('textarea.maxchars').each(function() {
            maxChars(this);
        })
    }

    loadErrorCallback(err) {
        if (err.message) {
            let exceptionType = $(err.message).find('.exception').data('exception');
            if (exceptionType === 'process-not-found')
                location.reload();
            else {
                location.reload();
                console.log('EXCEPTION thrown: ' + exceptionType);
            }
        }
        else
            console.log('Ajax error', err);
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
            this.loadCall(url, 'POST', $.param(sendData)).then((response) => this.callback(response));
        }).on('click', '.button-cancel', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.callback()
        });
    }
}

export default DialogHandler

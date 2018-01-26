import $ from 'jquery'
import FormValidationView from './formValidationHandler'

class ExceptionHandler {

    constructor (element, options) {
        this.$main = $(element)
        this.message = options.message;
        this.code = options.code;
        this.callback = options.callback;
        this.bindEvents();
        this.render();
    }

    render() {
        var message = $(this.message).filter('div.exception');
        if (message.length == 0) {
            message = $(this.message).find('div.exception');
        }
        this.$main.find('.dialog','.exception').remove();
        this.$main.find('.body').prepend(message.get(0).outerHTML);
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

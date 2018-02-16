import $ from 'jquery'

class ExceptionHandler {

    constructor (element, options) {
        this.$main = $(element)
        this.message = options.message;
        this.code = options.code;
        this.callback = options.callback || this.closeException;
        this.bindEvents();
        this.render();
    }

    render() {
        var $message = $(this.message).filter('div.exception');
        this.$messageElement = $($message.get(0).outerHTML);
        if ($message.length == 0) {
            $message = $(this.message).find('div.exception');
        }
        this.$main.find('.dialog','.exception').remove();
        this.$main.find('.body').first().prepend(this.$messageElement);
        var $buttons = this.$messageElement.find('.btn');
        if ($buttons.length == 0) {
            $buttons = $('<a class="btn button-cancel right">X</a>');
            this.$messageElement.find('.header').append($buttons);
        }
        $buttons.on('click', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.callback($(ev.target).attr('href'));
        })
        setTimeout(() => {
            this.closeException();
        },8000)
    }

    closeException() {
        if (this.$messageElement) {
            this.$messageElement.remove();
        }
    }

    bindEvents() {
    }
}

export default ExceptionHandler

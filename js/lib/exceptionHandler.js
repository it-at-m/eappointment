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
        if ($message.length == 0) {
            $message = $(this.message).find('div.exception');
        }
        this.$main.find('.dialog','.exception').remove();
        this.$messageElement = $($message.get(0).outerHTML)
        this.$main.find('.body').prepend(this.$messageElement);
        var $buttons = this.$messageElement.find('.btn');
        if ($buttons.length == 0) {
            $buttons = $('<a class="btn button-cancel right">X</a>');
            this.$messageElement.append($buttons);
        }
        $buttons.on('click', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.callback($(ev.target).attr('href'));
        })
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

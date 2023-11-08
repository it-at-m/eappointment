import $ from 'jquery'

class ExceptionHandler {

    constructor(element, options) {
        this.$main = $(element)
        this.message = options.message;
        this.parent = options.parent || this.$main;
        this.code = options.code;
        this.callback = options.callback || this.closeException;
        this.bindEvents();
        this.render();
    }

    render() {
        var $message = $(this.message).filter('.exception');
        if ($message.length == 0) {
            $message = $(this.message).find('.exception');
        }

        this.$messageElement = $($message.get(0).outerHTML);

        this.$main.find('.dialog, .exception').remove();
        this.$main.find('.body').first().prepend(this.$messageElement);

        var $buttons = this.$messageElement.find('.btn');
        if ($buttons.length == 0) {
            $buttons = $('<button class="button button--diamond btn button-cancel close right" title="Schließen"><i class="fas fa-times" aria-hidden="true"></i> <span class="aural">Schließen</span></button>');
            this.$messageElement.find('.header').append($buttons);
        }
        $buttons.on('click', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            var callback = $(ev.target).data('callback');
            if (this.parent[callback]) {
                this.callback = this.parent[callback];
            }
            this.callback(ev);
        })
        /*setTimeout(() => {
            this.closeException();
        },8000)
        */
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

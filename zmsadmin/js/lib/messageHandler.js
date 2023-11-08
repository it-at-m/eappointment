import $ from 'jquery';
import ExceptionHandler from './exceptionHandler'
import DialogHandler from './dialogHandler'

class MessageHandler {

    constructor(element, options) {
        this.$main = $(element)
        this.message = options.message;
        this.returnTarget = options.returnTarget;
        this.parent = options.parent;
        this.callback = options.callback || (() => { });
        this.handleLightbox = options.handleLightbox || (() => { });
        this.bindEvents();
        this.render()
        //console.log('messageHandler.js');
    }

    render() {
        var content = $(this.message).filter('.dialog');
        if (content.length == 0) {
            var message = $(this.message).find('.dialog');
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
                this.addFocusTrap(this.$main);
            } else {
                this.$main.find('.body').prepend(content.get(0).outerHTML);
            }
        }
    }

    bindEvents() {
        this.$main.off().on('click', '.button-ok', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.removeFocusTrap(this.$main);
            this.callback();
        }).on('click', '.button-abort', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.removeFocusTrap(this.$main);
            this.handleLightbox();
        }).on('click', '.button-callback', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            var callback = $(ev.target).data('callback');
            this.callback = this.parent[callback];
            this.removeFocusTrap(this.$main);
            this.callback(ev);
            this.handleLightbox();
        }).on('keydown', (ev) => {
            switch(ev.key) {
                case 'Escape': // ESC    
                ev.preventDefault();
                ev.stopPropagation();
                this.removeFocusTrap(this.$main);
                this.callback();
                break;
            }
        });
    }

    removeFocusTrap(elem) {
        var tabbable = elem.find('select, input, textarea, button, a, *[role="button"]');
        tabbable.unbind('keydown');
    }

    addFocusTrap(elem) {
        // Get all focusable elements inside our trap container
        var tabbable = elem.find('select, input, textarea, button, a, *[role="button"]');
        // Focus the first element
        if (tabbable.length ) {
            tabbable.filter(':visible').first().focus();
            //console.log(tabbable.filter(':visible').first());
        }
        tabbable.bind('keydown', function (e) {
            if (e.keyCode === 9) { // TAB pressed
                // we need to update the visible last and first focusable elements everytime tab is pressed,
                // because elements can change their visibility
                var firstVisible = tabbable.filter(':visible').first();
                var lastVisible = tabbable.filter(':visible').last();
                if (firstVisible && lastVisible) {
                    if (e.shiftKey && ( $(firstVisible)[0] === $(this)[0] ) ) {
                        // TAB + SHIFT pressed on first visible element
                        e.preventDefault();
                        lastVisible.focus();
                    } 
                    else if (!e.shiftKey && ( $(lastVisible)[0] === $(this)[0] ) ) {
                        // TAB pressed pressed on last visible element
                        e.preventDefault();
                        firstVisible.focus();
                    }
                }
            }
        });
    }

}

export default MessageHandler

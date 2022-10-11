import $ from 'jquery';
import ExceptionHandler from './exceptionHandler'
import maxChars from '../element/form/maxChars'
import settings from '../settings'

class DialogHandler {

    constructor(element, options) {
        this.$main = $(element);
        this.response = options.response;
        this.callback = options.callback || (() => { });
        this.abortCallback = options.abortCallback || (() => { });
        this.escapeCallback = options.abortCallback || options.callback;
        this.returnTarget = options.returnTarget;
        this.parent = options.parent;
        this.loader = options.loader || (() => { });
        this.render();
        this.bindEvents();
        //console.log('dialogHandler.js');
    }

    render() {
        DialogHandler.hideMessages(false);
        var content = $(this.response).filter('.dialog');
        if (content.length == 0) {
            var message = $(this.response).find('.dialog');
            if (message.length > 0) {
                content = message;
            }
        }
        if (content.length == 0) {
            new ExceptionHandler(this.$main, { 'message': this.message, 'callback': this.callback });
        } else {
            this.$main.html(content);
            this.addFocusTrap(this.$main);
        }

        $('textarea.maxchars').each(function () {
            maxChars(this);
        });
    }

    bindEvents() {
        this.$main.off().on('click', '.button-ok', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.callback(ev);
            this.removeFocusTrap(this.$main);
        }).on('click', '.button-abort', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.abortCallback(ev);
            this.removeFocusTrap(this.$main);
        }).on('click', '.button-callback', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            var callback = $(ev.target).data('callback');
            this.callback = this.parent[callback];
            this.callback(ev);
            this.removeFocusTrap(this.$main);
        }).on('keydown', (ev) => {
            switch(ev.key) {
            case 'Escape': // ESC    
                ev.preventDefault();
                ev.stopPropagation();
                this.abortCallback(ev);
                this.removeFocusTrap(this.$main);
                break;
            }
        });
    }

    static hideMessages(instant = false) {
        let message = $.find('.message, .dialog');
        if (message.length && !instant) {
            setTimeout(() => {
                // we dont want to remove messages
                //$(message).not('.message-keep').fadeOut().remove();
            }, settings.hideMessageTime * 1000)
        } else if (message.length && instant) {
            $(message).not('.message-keep').fadeOut().remove();
        }
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

export default DialogHandler

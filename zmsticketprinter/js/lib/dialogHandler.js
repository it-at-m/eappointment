import $ from 'jquery';

class DialogHandler {

    constructor (element, options) {
        this.$main = $(element);
        this.response = options.response;
        this.callback = options.callback || (() => {});
        this.parent = options.parent;
        this.handleLightbox = options.handleLightbox || (() => {});
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
        this.$main.html(content);
    }

    bindEvents() {
        this.$main.off().on('click', '.button-ok', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.callback(ev);
        }).on('click', '.button-abort', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.handleLightbox();
        }).on('click', '.button-callback', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            var callback = $(ev.target).data('callback');
            this.callback = this.parent[callback];
            this.callback(ev);
        });
    }
}

export default DialogHandler

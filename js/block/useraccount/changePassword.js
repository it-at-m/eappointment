import BaseView from "../../lib/baseview"
import $ from "jquery"
import { lightbox } from '../../lib/utils'
import FormValidationView from '../form-validation'
import ExceptionHandler from '../../lib/exceptionHandler'
import MessageHandler from '../../lib/messageHandler';

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.$main = $(element);
        this.includeUrl = options.includeUrl || "";
        this.onSaveProcess = options.onSaveProcess || (() => {});
        $.ajaxSetup({ cache: false });
        this.bindEvents();
    }

    save (ev) {
        console.log("Save Button clicked", ev);
        const sendData = this.$main.find('form').serialize();
        const url = `${this.includeUrl}/profile/`;
        return this.loadCall(url, 'POST', sendData);
    }

    bindEvents() {
        this.$main.off().on('click', '.form-actions button.button-save', (ev) => {
            event.preventDefault();
            event.stopPropagation();
            this.save(ev).then((response) => {
                this.loadMessage(response, this.onSaveProcess);
            }).catch(err => this.loadErrorCallback(err));
        })
    }

    loadMessage (response, callback) {
        const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {
            destroyLightbox();
            this.cleanReload();
        });
        new MessageHandler(lightboxContentElement, {
            message: response
        });
    }

    loadErrorCallback(err) {
        if (err.message.includes('data-exception-errorlist')) {
            new FormValidationView(this.$main.find('form'), {
                responseJson: $(err.message).find('[data-exception-errorlist]').data('exception-errorlist')
            });
        }
    }
}

export default View;

import BaseView from "../../lib/baseview"
import $ from "jquery"
import { lightbox } from '../../lib/utils'
import FormValidationView from '../../lib/formValidationHandler'
import MessageHandler from '../../lib/messageHandler';
import ActionHandler from "../../block/appointment/action"

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.$main = $(element);
        this.includeUrl = options.includeUrl || "";
        this.onSaveProcess = options.onSaveProcess || (() => {});
        this.dataUrl = options.dataUrl;
        this.ActionHandler = new ActionHandler(element, options);
        $.ajaxSetup({ cache: false });
        this.bindEvents();
    }

    save (ev) {
        console.log("Save Button clicked", ev);
        const sendData = this.$main.find('form').serialize();
        return this.loadCall(this.dataUrl, 'POST', sendData);
    }

    bindEvents() {
        this.$main.off().on('click', '.form-actions button.button-save', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.save(event).then((response) => {
                this.loadMessage(response, this.onSaveProcess);
            }).catch(err => this.loadErrorCallback(err));
        })
    }

    loadMessage (response, callback) {
        if (response) {
            const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {callback()});
            new MessageHandler(lightboxContentElement, {
                message: response,
                callback: (ActionHandler, buttonUrl, ev) => {
                    if (ActionHandler) {
                        let promise = this.ActionHandler[ActionHandler](ev);
                        if (promise instanceof Promise) {
                            promise
                                .then((response) => {this.loadMessage(response, callback)})
                                .catch(err => this.loadErrorCallback(err))
                        } else {
                            callback();
                        }
                    } else if (buttonUrl) {
                        this.loadByCallbackUrl(buttonUrl);
                        callback();
                    }
                    destroyLightbox();
                }
            })
        }
    }

    loadErrorCallback(err) {
        if (err.message.includes('data-exception-errorlist')) {
            let exceptionData = $(err.message).find('[data-exception-errorlist]').data('exception-errorlist');
            new FormValidationView(this.$main.find('form'), {
                responseJson: exceptionData
            });
        }
        else if (err.message.toLowerCase().includes('exception')) {
            let exceptionType = $(err.message).filter('.exception').data('exception');
            if (exceptionType === 'reservation-failed') {
                this.FreeProcessList.loadList();
                this.FormButtons.load();
            }
            else {
                this.cleanReload()
                console.log('EXCEPTION thrown: ' + exceptionType);
            }
        }
        else {
            console.log('Ajax error', err);
        }
    }
}

export default View;

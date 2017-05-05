import BaseView from "../../lib/baseview"
import $ from "jquery"
import { lightbox } from '../../lib/utils'
import ButtonActionHandler from "../appointment/action"
import MessageHandler from '../../lib/messageHandler';
import DialogHandler from '../../lib/dialogHandler';

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        console.log('Component: Pickup Table', this, options);
        this.ButtonAction = new ButtonActionHandler(element, options);
        this.includeUrl = options.includeUrl || "";
        this.onFinishProcess = options.onFinishProcess || (() => {});
        this.onPickupCallProcess = options.onPickupCallProcess || (() => {});
        this.onMailSent = options.onMailSent || (() => {});
        this.onNotificationSent = options.onNotificationSent || (() => {});
        this.bindPublicMethods('bindEvents');
        $.ajaxSetup({ cache: false });
        this.loadDirectCall();
        this.bindEvents();
    }

    loadDirectCall() {
        const processId = this.$main.filter('[data-selectedprocess]').data('selectedprocess');
        if (processId) {
            this.ButtonAction.pickupDirect(processId).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onPickupCallProcess);
            });
        }
    }

    loadErrorCallback(err) {
        if (err.message) {
            let exceptionType = $(err.message).find('.exception').data('exception');
            console.log('EXCEPTION thrown: ' + exceptionType);
        }
        else {
            console.log('Ajax error', err);
        }
        this.ButtonAction.cancel().then(this.cleanReload());
    }

    loadMessage (response, callback) {
        if (response) {
            const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {
                this.ButtonAction.cancel();
                callback();
            });
            new MessageHandler(lightboxContentElement, {
                message: response,
                callback: (buttonAction, buttonUrl, ev) => {
                    if (buttonAction) {
                        var newPromise = this.ButtonAction[buttonAction](ev);
                        newPromise.catch(err => this.loadErrorCallback(err)).then((response) => {
                            this.loadMessage(response, callback);
                        }).then(this.cleanReload());
                    } else if (buttonUrl) {
                        this.loadByCallbackUrl(buttonUrl)
                    }
                    destroyLightbox();
                }
            })
        }
    }

    loadDialog (response, callback) {
        const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {callback()})
        new DialogHandler(lightboxContentElement, {
            response: response,
            callback: (message) => {
                if (message) {
                    if ($(message).find('.dialog form').length > 0) {
                        this.loadDialog(message, callback);
                    }
                    else {
                        this.loadMessage(message, callback);
                    }
                }
                destroyLightbox();
            }
        })
    }

    loadByCallbackUrl(url) {
        this.loadPromise = this.loadCall(url).catch(err => this.loadErrorCallback(err));
        return this.loadPromise;
    }

    bindEvents() {
        this.$main.off('click').on('change', '.switchcluster select', (ev) => {
            $(ev.target).closest('form').submit();
        }).on('click', 'a.process-finish', (ev) => {
            this.ButtonAction.finish(ev).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onFinishProcess);
            });
        }).on('click', 'a.process-finish-list', (ev) => {
            this.ButtonAction.finishList(ev).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onFinishProcess);
            });
        }).on('click', 'a.process-pickup', (ev) => {
            this.ButtonAction.pickup(ev).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onPickupCallProcess);
            });
        }).on('click', '.process-notification-send', (ev) => {
            const url = `${this.includeUrl}/pickup/notification/`;
            this.ButtonAction.sendNotification(ev, url).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onNotificationSent);
            });
        }).on('click', '.process-custom-notification-send', (ev) => {
            const url = `${this.includeUrl}/notification/`;
            this.ButtonAction.sendNotification(ev, url).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadDialog(response, this.onNotificationSent);
            });
        }).on('click', '.process-mail-send', (ev) => {
            const url = `${this.includeUrl}/pickup/mail/`;
            this.ButtonAction.sendMail(ev, url).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onMailSent);
            });
        }).on('click', '.process-custom-mail-send', (ev) => {
            const url = `${this.includeUrl}/mail/`;
            this.ButtonAction.sendMail(ev, url).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadDialog(response, this.onMailSent);
            });
        })
    }
}

export default View;

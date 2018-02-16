import BaseView from "../../lib/baseview"
import $ from "jquery"
import ActionHandler from "../appointment/action"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        console.log('Component: Pickup Table', this, options);
        this.ActionHandler = new ActionHandler(element, options);
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
        const processId = this.$main.find('[data-selectedprocess]').data('selectedprocess');
        if (processId) {
            this.ActionHandler.pickupDirect(processId).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onPickupCallProcess);
            });
        }
    }

    bindEvents() {
        this.$main.off('click').on('change', '.switchcluster select', (ev) => {
            $(ev.target).closest('form').submit();
        }).on('click', 'a.process-finish', (ev) => {
            const id  = $(ev.target).data('id')
            const name  = $(ev.target).data('name')
            var confirmFinish = this.loadCall(`${this.includeUrl}/dialog/?template=confirm_finish&parameter[id]=${id}&parameter[name]=${name}`);
            confirmFinish.catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onFinishProcess);
            });
        }).on('click', 'a.process-finish-list', () => {
            var confirmFinishList = this.loadCall(`${this.includeUrl}/dialog/?template=confirm_finish_list`);
            confirmFinishList.catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onFinishProcess);
            });
        }).on('click', 'a.process-pickup', (ev) => {
            this.ActionHandler.pickup(ev).then((response) => {
                this.loadMessage(response, this.onPickupCallProcess);
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '.process-notification-send', (ev) => {
            const url = `${this.includeUrl}/pickup/notification/`;
            this.ActionHandler.sendNotification(ev, url).then((response) => {
                this.loadMessage(response, this.onNotificationSent);
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '.process-custom-notification-send', (ev) => {
            const url = `${this.includeUrl}/notification/`;
            this.ActionHandler.sendNotification(ev, url).then((response) => {
                this.loadDialog(response, this.onNotificationSent);
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '.process-mail-send', (ev) => {
            const url = `${this.includeUrl}/pickup/mail/`;
            this.ActionHandler.sendMail(ev, url)
                .then((response) => {
                    this.loadMessage(response, this.onMailSent)
                })
                .catch(err => this.loadErrorCallback(err));
        }).on('click', '.process-custom-mail-send', (ev) => {
            const url = `${this.includeUrl}/mail/`;
            this.ActionHandler.sendMail(ev, url)
                .then((response) => {
                    this.loadDialog(response, this.onMailSent)
                })
                .catch(err => this.loadErrorCallback(err));
        });
    }
}

export default View;

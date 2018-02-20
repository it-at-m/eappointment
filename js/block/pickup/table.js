import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.setOptions(options);
        this.setCallbacks(options);
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        this.load();
    }

    setOptions(options) {
        this.includeUrl = options.includeUrl || "";
    }

    setCallbacks(options) {
        this.onChangeTableView = options.onChangeTableView;
        this.onConfirm = options.onConfirm;
        this.onPickupCall = options.onPickupCall;
        this.onFinishProcess = options.onFinishProcess;
        this.onCancelProcess = options.onCancelProcess;
        this.onFinishProcessList = options.onFinishProcessList;
        this.onMailSent = options.onMailSent;
        this.onNotificationSent = options.onNotificationSent;
    }

    load() {
        const processId = this.$main.find('[data-selectedprocess]').data('selectedprocess');
        if (processId) {
            this.pickupDirect(processId).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onPickupCallProcess);
            });
        } else {
            return this.loadContent(`${this.includeUrl}/pickup/queue/`, 'GET').catch(err => this.loadErrorCallback(err));
        }

    }

    pickupDirect (processId) {
        const url = `${this.includeUrl}/pickup/call/${processId}/`
        return this.loadCall(url);
    }

    bindEvents() {
        this.$main.off('click').on('change', '.pickup-table .switchcluster select', (ev) => {
            this.onChangeTableView(ev);
        }).on('click', 'a.process-finish', (ev) => {
            this.onConfirm(ev, "confirm_finish", () => {this.onFinishProcess(ev)});
        }).on('click', 'a.process-finish-list', (ev) => {
            this.onConfirm(ev, "confirm_finish_list", () => {this.onFinishProcessList(ev)});
        }).on('click', 'a.process-pickup', (ev) => {
            this.onPickupCall(ev, () => {this.onFinishProcess(ev)});

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

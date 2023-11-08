import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor(element, options) {
        super(element, options);
        this.$main = $(element);
        this.setOptions(options);
        this.setCallbacks(options);
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        this.load();
    }

    setOptions(options) {
        this.includeUrl = options.includeUrl || "";
        this.selectedProcess = options.selectedProcess;
        this.selectedScope = options.selectedScope;
        this.limit = options.limit;
        this.offset = options.offset;
    }

    setCallbacks(options) {
        this.onConfirm = options.onConfirm;
        this.onReloadQueue = options.onReloadQueue;
        this.onLoadNextQueue = options.onLoadNextQueue;
        this.onPickupCall = options.onPickupCall;
        this.onFinishProcess = options.onFinishProcess;
        this.onCancelProcess = options.onCancelProcess;
        this.onFinishProcessList = options.onFinishProcessList;
        this.onNotificationSent = options.onNotificationSent;
        this.onNotificationCustomSent = options.onNotificationCustomSent;
        this.onMailSent = options.onMailSent;
        this.onMailCustomSent = options.onMailCustomSent;
        this.onProcessNotFound = options.onProcessNotFound;
        this.onChangeScope = options.onChangeScope;
    }

    load() {
        if (this.selectedProcess) {
            this.loadCall(`${this.includeUrl}/pickup/call/${this.selectedProcess}/`).then(() => {
                this.onPickupCall(
                    null, 
                    () => {
                        this.onFinishProcess(null, this.selectedProcess);
                    }, 
                    () => {
                        this.onCancelProcess(null);
                    }, 
                    this.selectedProcess);
            });
        } else {
            this.loadContent(`${this.includeUrl}/pickup/queue/?selectedscope=${this.selectedScope}&limit=${this.limit}&offset=${this.offset}`, 'GET');
        }

    }

    bindEvents() {
        this.$main.off('click').on('click', 'a.process-finish', (ev) => {
            this.onConfirm(ev, "confirm_finish", () => { this.onFinishProcess(ev) });
        }).on('click', '.pickup-table a.queue-reload', (ev) => {
            this.onReloadQueue(ev);
        }).on('click', 'a.process-finish-list', (ev) => {
            this.onConfirm(ev, "confirm_finish_list", () => { this.onFinishProcessList(ev) });
        }).on('click', 'a.load-next-queue', (ev) => {
            this.onLoadNextQueue(ev);
        }).on('click', 'a.process-pickup', (ev) => {
            this.onPickupCall(ev, () => { this.onFinishProcess(ev)}, () => { this.onCancelProcess(ev) });
        }).on('click', '.process-notification-send', (ev) => {
            this.onNotificationSent(ev);
        }).on('click', '.process-custom-notification-send', (ev) => {
            this.onNotificationCustomSent(ev);
        }).on('click', '.process-mail-send', (ev) => {
            this.onMailSent(ev);
        }).on('click', '.process-custom-mail-send', (ev) => {
            this.onMailCustomSent(ev);
        }).on('focus', '.pickup-table .change-scope select', (ev) => {
            this.selectedScope = ev.target.value;
        }).on('change', '.pickup-table .change-scope select', (ev) => {
            this.onChangeScope(ev, () => {
                this.$main.find('.pickup-table .switchcluster select').val(this.selectedScope);
            });
        });
    }
}

export default View;

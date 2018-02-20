import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor (element, options) {
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
    }

    setCallbacks(options) {
        this.onChangeTableView = options.onChangeTableView;
        this.onPickupCall = options.onPickupCall;
        this.onFinishProcess = options.onFinishProcess;
        this.onCancelProcess = options.onCancelProcess;
        this.onProcessNotFound = options.onProcessNotFound;
    }

    load() {
        if (this.selectedProcess) {
            this.loadCall(`${this.includeUrl}/pickup/call/${this.selectedProcess}/`).then(() => {
                this.onPickupCall(null, () => {
                    this.onFinishProcess(null, this.selectedProcess);
                }, this.selectedProcess);
            });
        } else {
            this.loadContent(`${this.includeUrl}/pickup/queue/?handheld=1`, 'GET');
        }

    }

    bindEvents() {
        this.$main.off('click').on('click', 'a.process-pickup', (ev) => {
            this.onPickupCall(ev, () => {this.onFinishProcess(ev)});
        });
    }
}

export default View;

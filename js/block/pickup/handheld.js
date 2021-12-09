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
        $(this.load());
    }

    setOptions(options) {
        this.includeUrl = options.includeUrl || "";
        this.selectedProcess = options.selectedProcess;
        this.limit = options.limit;
        this.offset = options.offset;
    }

    setCallbacks(options) {
        this.onPickupCall = options.onPickupCall;
        this.onFinishProcess = options.onFinishProcess;
        this.onCancelProcess = options.onCancelProcess;
        this.onProcessNotFound = options.onProcessNotFound;
        this.onLoadNextQueue = options.onLoadNextQueue;
    }

    bindEvents() {
        this.$main.off('click').on('click', 'a.process-pickup', (ev) => {
            this.onPickupCall(ev, () => { this.onFinishProcess(ev) });
        }).on('click', 'a.load-next-queue', (ev) => {
            this.onLoadNextQueue(ev);
        });
    }

    load() {
        if (this.selectedProcess) {
            this.loadContent(`${this.includeUrl}/pickup/queue/?handheld=1&limit=${this.limit}&offset=${this.offset}`, 'GET').then(() => {
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
            this.loadContent(`${this.includeUrl}/pickup/queue/?handheld=1&limit=${this.limit}&offset=${this.offset}`, 'GET');
        }
    }
}

export default View;

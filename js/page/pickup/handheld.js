import BaseView from '../../lib/baseview'
import { hideSpinner } from '../../lib/utils'
import PickupHandheldView from '../../block/pickup/handheld'


class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.$main = $(element);
        this.selectedProcess = options['selected-process'];
        this.includeUrl = options.includeurl;
        this.bindPublicMethods(
            'bindEvents',
            'onFinishProcess',
            'onPickupCall',
            'onCancelProcess',
            'onProcessNotFound'
        );
        this.loadAllPartials().then(() => this.bindEvents());
    }

    bindEvents() {}

    onCancelProcess (event) {
        this.selectedProcess = null;
        stopEvent(event);
        return this.loadCall(`${this.includeUrl}/pickup/call/cancel/`).then((response) => {
            this.loadMessage(response, () => {
                this.loadAllPartials();
            });
        });
    }

    onFinishProcess (event, processId) {
        this.selectedProcess = null;
        if (event) {
            stopEvent(event);
            processId  = $(event.target).data('id')
        }
        showSpinner(this.$main);
        this.loadCall(`${this.includeUrl}/pickup/delete/${processId}/`, 'DELETE').then((response) => {
              this.loadMessage(response, () => {
                  this.loadAllPartials();
              });
        });
    }

    onPickupCall(event, callback, processId) {
        if (event) {
            stopEvent(event);
            processId  = $(event.target).data('id')
        }
        return this.loadCall(`${this.includeUrl}/pickup/call/${processId}/`).then((response) => {
                this.loadDialog(response, callback);
            }
        );
    }

    onProcessNotFound () {
        this.selectedProcess = null;
        this.loadAllPartials();
    }

    loadAllPartials() {
        let promise = Promise.all([
            this.loadHandheldTable()
        ])
        return promise;
    }

    loadHandheldTable () {
        hideSpinner(this.$main);
        return new PickupHandheldView(this.$main, {
            includeUrl: this.includeUrl,
            selectedProcess: this.selectedProcess,
            onFinishProcess: this.onFinishProcess,
            onPickupCall: this.onPickupCall,
            onCancelProcess: this.onCancelProcess,
            onProcessNotFound: this.onProcessNotFound
        });
    }

}

export default View;

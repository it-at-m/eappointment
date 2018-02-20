import BaseView from '../../lib/baseview'
import { stopEvent, showSpinner } from '../../lib/utils'
import PickupTableView from '../../block/pickup/table'
import $ from 'jquery'


class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.$main = $(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.bindPublicMethods(
            'bindEvents',
            'onChangeTableView',
            'onConfirm',
            'loadPickupTable',
            'onFinishProcess',
            'onFinishProcessList',
            'onPickupCall',
            'onMailSent',
            'onNotificationSent',
            'onCancelProcess'
        );
        this.loadAllPartials().then(() => this.bindEvents());
    }

    bindEvents() {}

    onChangeTableView (event) {
        $(event.target).closest('form').submit();
    }

    onConfirm(event, template, callback)
    {
      stopEvent(event);
      this.selectedProcess = null;
      const processId  = $(event.target).data('id');
      const name  = $(event.target).data('name');
      this.loadCall(`${this.includeUrl}/dialog/?template=${template}&parameter[id]=${processId}&parameter[name]=${name}`).then((response) => {
          this.loadDialog(response, callback);
      });
    }

    onFinishProcess (event) {
        stopEvent(event);
        showSpinner(this.$main);
        const processId  = $(event.target).data('id');
        this.loadCall(`${this.includeUrl}/pickup/delete/${processId}/`, 'DELETE').then((response) => {
              this.loadMessage(response, () => {
                  this.loadAllPartials();
              });
        });
    }

    onCancelProcess (event) {
        stopEvent(event);
        return this.loadCall(`${this.includeUrl}/pickup/call/cancel/`).then((response) => {
            this.loadMessage(response, () => {
                this.loadAllPartials();
            });
        });
    }

    onFinishProcessList(event) {
        stopEvent(event);
        showSpinner(this.$main);
        var idList = this.$main.find(".process-finish").map((index, item) => {
            return $(item).data('id');
        }).get();
        var deleteFromQueue = () => {
            let processId = idList.shift();
            if (processId) {
                let url = `${this.includeUrl}/pickup/delete/${processId}/`;
                if (idList.length == 0) {
                    url = url + "?list=1";
                    return this.loadCall(url, 'DELETE').then((response) => {
                        this.loadMessage(response, () => {
                            this.loadAllPartials();
                        });
                    });
                }
                return this.loadCall(url, 'DELETE').then(deleteFromQueue);
            }
        }
        return deleteFromQueue();
    }

    onPickupCall(event, callback) {
        stopEvent(event);
        const processId  = $(event.target).data('id')
        return this.loadCall(`${this.includeUrl}/pickup/call/${processId}/`).then((response) => {
                this.loadDialog(response, callback);
            }
        );
    }

    onMailSent () {
        this.cleanReload()
    }

    onNotificationSent () {
        this.cleanReload()
    }

    loadAllPartials() {
        let promise = Promise.all([
            this.loadPickupTable()
        ])
        return promise;
    }

    loadPickupTable () {
        return new PickupTableView(this.$main, {
            source: 'pickup',
            includeUrl: this.includeUrl,
            onChangeTableView: this.onChangeTableView,
            onConfirm: this.onConfirm,
            onFinishProcess: this.onFinishProcess,
            onFinishProcessList: this.onFinishProcessList,
            onPickupCall: this.onPickupCall,
            onMailSent: this.onMailSent,
            onNotificationSent: this.onNotificationSent,
            onCancelProcess: this.onCancelProcess
        })
    }

}

export default View;

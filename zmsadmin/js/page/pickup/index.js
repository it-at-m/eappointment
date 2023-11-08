import BaseView from '../../lib/baseview'
import { stopEvent, showSpinner, hideSpinner } from '../../lib/utils'
import PickupTableView from '../../block/pickup/table'
import $ from 'jquery'


class View extends BaseView {

    constructor(element, options) {
        super(element);
        this.$main = $(element);
        this.selectedProcess = options['selected-process'];
        this.selectedScope = options['selected-scope'];
        this.includeUrl = options.includeurl;
        this.limit = options.limit;
        this.offset = options.offset;
        this.bindPublicMethods(
            'bindEvents',
            'onConfirm',
            'loadPickupTable',
            'onLoadNextQueue',
            'onFinishProcess',
            'onFinishProcessList',
            'onPickupCall',
            'onNotificationSent',
            'onNotificationCustomSent',
            'onMailSent',
            'onMailCustomSent',
            'onCancelProcess',
            'onProcessNotFound',
            'onReloadQueue',
            'onChangeScope'
        );
        this.loadAllPartials().then(() => this.bindEvents());
    }

    bindEvents() { }

    onConfirm(event, template, callback) {
        stopEvent(event);
        this.selectedProcess = null;
        const processId = $(event.currentTarget).data('id');
        const name = $(event.currentTarget).data('name');
        var url = `${this.includeUrl}/dialog/?template=${template}`;
        if (processId || name) {
            url = url + `&parameter[id]=${processId}&parameter[name]=${name}`;
        }
        this.loadCall(url).then((response) => {
            this.loadDialog(response, callback, null, event.currentTarget);
        });
    }

    onCancelProcess(event) {
        this.selectedProcess = null;
        if (event) {
            stopEvent(event);
        }
        return this.loadCall(`${this.includeUrl}/pickup/call/cancel/`).then((response) => {
            this.loadMessage(response, () => {
                this.loadAllPartials();
            }, null, event.currentTarget);
        });
    }

    onFinishProcess(event, processId) {
        this.selectedProcess = null;
        if (event) {
            stopEvent(event);
            processId = $(event.currentTarget).data('id')
        }
        showSpinner(this.$main);
        this.loadCall(`${this.includeUrl}/pickup/delete/${processId}/`, 'GET').then((response) => {
            this.loadMessage(response, () => {
                this.loadAllPartials();
            }, null, event.currentTarget);
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
                    return this.loadCall(url, 'GET').then((response) => {
                        this.loadMessage(response, () => {
                            this.loadAllPartials();
                        }, null, event.currentTarget);
                    });
                }
                return this.loadCall(url, 'GET').then(deleteFromQueue);
            }
        }
        return deleteFromQueue();
    }

    onPickupCall(event, callback, abortCallback, processId) {
        let currentTarget = false;
        if (event) {
            stopEvent(event);
            processId = $(event.currentTarget).data('id')
            currentTarget = event.currentTarget
        }
        return this.loadCall(`${this.includeUrl}/pickup/call/${processId}/`).then((response) => {
            this.loadDialog(response, callback, abortCallback, currentTarget);
        }
        );
    }

    onMailSent(event) {
        stopEvent(event);
        showSpinner(this.$main);
        const processId = $(event.currentTarget).data('process');
        this.loadCall(`${this.includeUrl}/pickup/mail/?selectedprocess=${processId}`).then(
            (response) => this.loadMessage(response, () => {
                this.loadAllPartials();
            }, null, event.currentTarget)
        );
    }

    onMailCustomSent(event) {
        stopEvent(event);
        const processId = $(event.currentTarget).data('process');
        this.loadCall(`${this.includeUrl}/mail/?selectedprocess=${processId}&dialog=1`).then((response) => {
            this.loadDialog(response, (() => {
                showSpinner(this.$main);
                const sendData = $('.dialog form').serializeArray();
                sendData.push(
                    { 'name': 'submit', 'value': 'form' },
                    { 'name': 'dialog', 'value': 1 }
                );
                this.loadCall(`${this.includeUrl}/mail/`, 'POST', $.param(sendData)).then(
                    (response) => this.loadMessage(response, () => {
                        this.loadAllPartials();
                    }, null, event.currentTarget)
                );
            }), null, event.currentTarget)
        });
    }

    onNotificationSent(event) {
        stopEvent(event);
        showSpinner(this.$main);
        const processId = $(event.currentTarget).data('process');
        this.loadCall(`${this.includeUrl}/pickup/notification/?selectedprocess=${processId}`).then(
            (response) => this.loadMessage(response, () => {
                this.loadAllPartials();
            }, null, event.currentTarget)
        );
    }

    onNotificationCustomSent(event) {
        stopEvent(event);
        const processId = $(event.currentTarget).data('process');
        this.loadCall(`${this.includeUrl}/notification/?selectedprocess=${processId}&dialog=1`).then((response) => {
            this.loadDialog(response, (() => {
                showSpinner(this.$main);
                const sendData = $('.dialog form').serializeArray();
                sendData.push(
                    { 'name': 'submit', 'value': 'form' },
                    { 'name': 'dialog', 'value': 1 }
                );
                this.loadCall(`${this.includeUrl}/notification/`, 'POST', $.param(sendData)).then(
                    (response) => this.loadMessage(response, () => {
                        this.loadAllPartials();
                    }, null, event.currentTarget)
                );
            }), null, event.currentTarget)
        });
    }

    onProcessNotFound() {
        this.selectedProcess = null;
        this.loadAllPartials();
    }

    onReloadQueue(event) {
        stopEvent(event);
        this.offset = 0;
        this.selectedProcess = null;
        this.loadAllPartials();
    }

    onChangeScope(event) {
        stopEvent(event);
        this.offset = 0;
        this.selectedScope = $(event.currentTarget).val();
        this.loadPickupTable();
    }

    loadAllPartials() {
        let promise = Promise.all([
            this.loadPickupTable()
        ])
        return promise;
    }

    onLoadNextQueue(event) {
        stopEvent(event);
        this.limit = $(event.currentTarget).data('limit');
        this.offset = $(event.currentTarget).data('offset');
        this.loadPickupTable();
    }

    loadPickupTable() {
        hideSpinner(this.$main);
        return new PickupTableView(this.$main, {
            source: 'pickup',
            includeUrl: this.includeUrl,
            selectedProcess: this.selectedProcess,
            selectedScope: this.selectedScope,
            limit: this.limit,
            offset: this.offset,
            onConfirm: this.onConfirm,
            onReloadQueue: this.onReloadQueue,
            onFinishProcess: this.onFinishProcess,
            onFinishProcessList: this.onFinishProcessList,
            onPickupCall: this.onPickupCall,
            onNotificationSent: this.onNotificationSent,
            onNotificationCustomSent: this.onNotificationCustomSent,
            onMailSent: this.onMailSent,
            onMailCustomSent: this.onMailCustomSent,
            onCancelProcess: this.onCancelProcess,
            onProcessNotFound: this.onProcessNotFound,
            onChangeScope: this.onChangeScope,
            onLoadNextQueue: this.onLoadNextQueue
        })
    }

}

export default View;

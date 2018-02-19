import BaseView from '../../lib/baseview'
import { stopEvent } from '../../lib/utils'
import PickupTableView from '../../block/pickup/table'
import $ from 'jquery'


class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.bindPublicMethods(
            'bindEvents',
            'onChangeTableView',
            'onConfirm',
            'loadPickupTable',
            'onFinishProcess',
            'onPickupCallProcess',
            'onMailSent',
            'onNotificationSent'
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
        const processId  = $(event.target).data('id');
        this.loadCall(`${this.includeUrl}/pickup/delete/${processId}/`, 'DELETE').then((response) => {
              this.loadMessage(response, () => {
                  this.loadAllPartials();
              });
        });

    }

    onPickupCallProcess () {
        this.cleanReload()
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
            onPickupCallProcess: this.onPickupCallProcess,
            onMailSent: this.onMailSent,
            onNotificationSent: this.onNotificationSent
        })
    }

}

export default View;

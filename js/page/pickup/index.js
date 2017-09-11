import BaseView from '../../lib/baseview'
import PickupTableView from '../../block/pickup/table'
import $ from 'jquery'


class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.bindPublicMethods('bindEvents','loadPickupTable','onFinishProcess','onPickupCallProcess','onMailSent','onNotificationSent');
        this.$.ready(this.loadPickupTable);
        console.log('Page: Pickup', this, options);
    }

    bindEvents() {}

    onFinishProcess () {
        this.cleanReload()
    };

    onPickupCallProcess () {
        this.cleanReload()
    };

    onMailSent () {
        this.cleanReload()
    };

    onNotificationSent () {
        this.cleanReload()
    };

    loadPickupTable () {
        return new PickupTableView(this.$main, {
            source: 'pickup',
            includeUrl: this.includeUrl,
            onFinishProcess: this.onFinishProcess,
            onPickupCallProcess: this.onPickupCallProcess,
            onMailSent: this.onMailSent,
            onNotificationSent: this.onNotificationSent
        })
    }

}

export default View;

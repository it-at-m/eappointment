import Pickup from '../pickup'
import { stopEvent, hideSpinner } from '../../lib/utils'
import PickupHandheldView from '../../block/pickup/handheld'
import $ from 'jquery'

class View extends Pickup {

    constructor(element, options) {
        super(element, options);
        this.limit = options.limit;
        this.offset = options.offset;
        this.bindPublicMethods();
    }

    loadAllPartials() {
        let promise = Promise.all([
            this.loadHandheldTable()
        ])
        return promise;
    }

    onLoadNextQueue(event) {
        stopEvent(event);
        this.limit = $(event.currentTarget).data('limit');
        this.offset = $(event.currentTarget).data('offset');
        this.loadHandheldTable();
    }

    loadHandheldTable () {
        hideSpinner(this.$main);
        return new PickupHandheldView(this.$main, {
            includeUrl: this.includeUrl,
            selectedProcess: this.selectedProcess,
            limit: this.limit,
            offset: this.offset,
            onFinishProcess: this.onFinishProcess,
            onPickupCall: this.onPickupCall,
            onCancelProcess: this.onCancelProcess,
            onProcessNotFound: this.onProcessNotFound,
            onLoadNextQueue: this.onLoadNextQueue
        });
    }

}

export default View;

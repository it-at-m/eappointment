import Pickup from '../pickup'
import $ from 'jquery'
import { hideSpinner } from '../../lib/utils'
import PickupHandheldView from '../../block/pickup/handheld'

class View extends Pickup {

    constructor(element, options) {
        super(element, options);
        this.bindPublicMethods();
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

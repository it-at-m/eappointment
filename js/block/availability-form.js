
import BaseView from '../lib/baseview';
import $ from "jquery";
import settings from '../settings';

class View extends BaseView {

    constructor (element) {
        super(element);
        console.log("Form Availability");
        this.bindPublicMethods('submit');
        this.$.find('button.button-save').on('click', this.submit);
    }

    submit () {
        console.log("Button pressed");
        this.$.hide();
        return false;
    }
}

export default View;

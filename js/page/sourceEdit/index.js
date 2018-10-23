import BaseView from '../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {
    constructor(element, options) {
        super(element);
        this.$main = $(element);
        this.element = $(element).focus();
        this.includeUrl = options.includeurl;
        this.bindPublicMethods();
        this.$.ready(() => {
            this.bindEvents();
        });
    }

    bindEvents() { }
}

export default View;

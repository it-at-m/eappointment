import BaseView from '../../lib/baseview'
import $ from 'jquery'
import RequestFormView from './requestform'

class View extends BaseView {

    constructor (element) {
        super(element);
        this.element = $(element);
        this.serviceList = [];
        new RequestFormView(element);
    }
}

export default View;

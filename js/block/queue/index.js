import BaseView from '../../lib/baseview'
import $ from 'jquery'
import QueueTableView from './table'

class View extends BaseView {

    constructor (element) {
        super(element);
        this.element = $(element);
        new QueueTableView(element);
    }
}

export default View;

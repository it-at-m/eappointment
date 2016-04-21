
import jQuery from "jquery";
import ErrorHandler from './errorHandler';

class BaseView extends ErrorHandler {

    constructor(element) {
        super();
        this.$main = jQuery(element);
    }

    get $ () {
        return this.$main;
    }

}

export default BaseView;

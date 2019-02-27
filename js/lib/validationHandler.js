import $ from 'jquery';
import BaseView from './baseview'

class ValidationHandler extends BaseView {

    constructor(element, options) {
        super(element);
        this.$main = $(element);
        this.errors = [];
        this.response = options.response;
        this.bindPublicMethods(
            'getValidationErrorList',
            'hasErrors',
            'render'
        );
        this.bindEvents();
        this.getValidationErrorList(this.response);
    }

    render() {
        console.log(this.errors)
    }

    bindEvents() {
    }

    getValidationErrorList(response) {
        this.errors = Object.entries(response).filter(item => 1 < item.filter(error => null !== error.messages).length);
    }

    hasErrors() {
        return (this.errors) ? true : false;
    }
}

export default ValidationHandler

import $ from 'jquery';
import BaseView from './baseview'

class ValidationHandler extends BaseView {

    constructor(element, options) {
        super(element);
        this.scope = element;
        this.$main = element.$main;
        this.errors = {};
        this.response = options.response;
        this.bindPublicMethods(
            'getValidationErrorList',
            'hasErrors',
            'render'
        );
        this.getValidationErrorList();
    }

    render() {
        Object.entries(this.errors).forEach((item, key) => {
            this.$main.find(`input[name^="${key}"]`).each((index, element) => {
                if (index > 0) {
                    return false;
                }
                $(element).closest('.form-group').addClass('has-error')
                $(element).closest('.controls').append(this.createDomList(item, key))
            })
        })
        this.scope.bindEvents()
    }

    createDomList(item, key) {
        var list = document.createElement("ul");
        list.classList.add(`error-list`);
        list.classList.add(`list--clean`);
        list.classList.add(`message`);
        list.classList.add(`message--error`);
        list.setAttribute(`role`, `alert`);
        Object.entries(item.messages).forEach((messageElement) => {
            var listItem = document.createElement("li")
            listItem.setAttribute('data-key', key);
            listItem.appendChild(document.createTextNode(messageElement.message));
            list.appendChild(listItem)
        })
        return list;
    }

    getValidationErrorList() {
        $("ul.error-list").remove();
        $(".has-error").removeClass("has-error");
        Object.entries(this.response).forEach((item, key) => {
            if (item.failed) {
                Object.assign(this.errors, { [key]: item });
            }
        }, {});
    }

    hasErrors() {
        return (0 < Object.keys(this.errors).length) ? true : false;
    }
}

export default ValidationHandler

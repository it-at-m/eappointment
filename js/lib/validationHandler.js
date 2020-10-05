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
        Object.keys(this.errors).forEach(key => {
            this.$main.find(`input[name^="${key}"]`).each((index, element) => {
                if (index > 0) {
                    return false;
                }
                $(element).closest('.form-group').addClass('has-error')
                $(element).closest('.controls').append(this.createDomList(this.errors[key], key, element))
            })
        })
        this.scope.bindEvents()
    }

    createDomList(item, key, element) {
        let labelId = element.getAttribute('id') || (key == 'requests') ? 'deselect-' + key : key ;
        let list = document.createElement("ul");
        list.classList.add(`error-list`);
        list.classList.add(`list--clean`);
        list.classList.add(`message`);
        list.classList.add(`message--error`);
        list.setAttribute(`role`, `alert`);
        list.setAttribute(`aria-describedby`, labelId)
        Object.values(item.messages).forEach((messageElement) => {
            let listItem = document.createElement("li")
            listItem.setAttribute('data-key', key);
            listItem.appendChild(document.createTextNode(messageElement.message));
            list.appendChild(listItem)
        })
        return list;
    }

    getValidationErrorList() {
        $("ul.error-list").remove();
        $(".has-error").removeClass("has-error");
        Object.entries(this.response).forEach((item) => {
            if (item[1].failed) {
                Object.assign(this.errors, { [item[0]]: item[1] });
            }
        }, {});
    }

    hasErrors() {
        return (0 < Object.keys(this.errors).length) ? true : false;
    }
}

export default ValidationHandler

import BaseView from '../../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element) {
        super(element);
        this.element = $(element);
        this.cleanUpLists(this.element);
        this.bindEvents();
    }

    bindEvents() {
        this.element.on('change', '.appointment-form .checkboxselect input:checkbox', (event) => {
            this.addService($(event.target).val(), this.serviceListSelected);
            this.removeService($(event.target).val(), this.serviceList);
            this.updateList();
        }).on('change', '.appointment-form .checkboxdeselect input:checkbox', (event) => {
            this.removeService($(event.target).val(), this.serviceListSelected);
            this.addService($(event.target).val(), this.serviceList);
            this.updateList();
        }).on('click', '.appointment-form .clear-list', () => {
            this.cleanUpLists(this.element);
            this.updateList();
        })
    }

    /**
     * update events after replacing list
     */
    updateList () {
        $('.appointment-form .checkboxdeselect input:checkbox').each((index, element) => {
            $(element).prop("checked", false);
            $(element).closest('label').hide();
            if ($.inArray($(element).val(), this.serviceListSelected) !== -1) {
                $(element).prop("checked", true);
                $(element).closest('label').show();
            }
        });

        $('.appointment-form .checkboxselect input:checkbox').each((index, element) => {
            $(element).prop("checked", false);
            $(element).closest('label').hide();
            if ($.inArray($(element).val(), this.serviceList) !== -1) {
                $(element).closest('label').show();
            }
        });
    }

    addService (value, listElement) {
        return listElement.push(value);
    }

    removeService (value, listElement) {
        for (var i = 0; i < listElement.length; i++)
           if (listElement[i] === value) {
              return listElement.splice(i,1);
        }
    }

    cleanUpLists (element)
    {
        this.serviceList = element.find('.appointment-form .checkboxselect input:checkbox').map(function() {
            return $(this).val();
        }).toArray();
        this.serviceListSelected = [];
    }
}

export default View;

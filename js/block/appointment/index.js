import BaseView from '../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.selectedDate = options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.selectDateWithOverlay = options.selectDateWithOverlay;
        this.element = $(element);
        this.serviceList = [];

        this.load().then(() => {
            this.cleanUpLists();
            this.bindEvents();
        });
    }

    load() {
        const url = `${this.includeUrl}/counter/appointmentForm/?selecteddate=${this.selectedDate}`
        this.loadPromise = this.loadContent(url)
        return this.loadPromise;
    }

    bindEvents() {
        this.$main.on('change', '.checkboxselect input:checkbox', (event) => {
            this.addService($(event.target).val(), this.serviceListSelected);
            this.removeService($(event.target).val(), this.serviceList);
            this.updateList();
        }).on('change', '.checkboxdeselect input:checkbox', (event) => {
            this.removeService($(event.target).val(), this.serviceListSelected);
            this.addService($(event.target).val(), this.serviceList);
            this.updateList();
        }).on('click', '.clear-list', () => {
            this.cleanUpLists();
            this.updateList();
        }).on('click', 'input[name=date]', (ev) => {
            console.log('date click')
            this.selectDateWithOverlay()
                   .then(date => ev.target.value = date)
                   .catch(() => console.log('no date selected'));
        })
    }

    /**
     * update events after replacing list
     */
    updateList () {
        this.$main.find('.checkboxdeselect input:checkbox').each((index, element) => {
            $(element).prop("checked", false);
            $(element).closest('label').hide();
            if ($.inArray($(element).val(), this.serviceListSelected) !== -1) {
                $(element).prop("checked", true);
                $(element).closest('label').show();
            }
        });

        this.$main.find('.checkboxselect input:checkbox').each((index, element) => {
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

    cleanUpLists ()
    {
        this.serviceList = this.$main.find('.checkboxselect input:checkbox').map(function() {
            return $(this).val();
        }).toArray();
        this.serviceListSelected = [];
    }
}

export default View;

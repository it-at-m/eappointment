import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor(element, options) {
        super(element, options);
        this.$main = $(element);
        this.selectedProcess = options.selectedProcess;
        this.selectedTime = options.selectedTime;
        this.slotsRequired = options.slotsRequired;
        this.serviceList = [];
        this.serviceListSelected = [];
        //console.log('Component: RequestList actions', this, options);
    }

    initRequestView(keepSelected = false) {
        if (this.selectedProcess || keepSelected === true)
            this.readList()
        else
            this.cleanLists()
    }

    /**
     * update events after replacing list
     */
    updateLists() {
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
        this.calculateSlotCount();
    }

    readList() {
        this.$main.find('.checkboxselect input:checked, .checkboxselect input:hidden').each((index, element) => {
            if ($.inArray($(element).val(), this.serviceListSelected) === -1)
                this.addServiceToList($(element), 'serviceListSelected')
        });
        this.$main.find('.checkboxdeselect input:not(:checked)').each((index, element) => {
            if ($.inArray($(element).val(), this.serviceList) === -1)
                this.addServiceToList($(element), 'serviceList')
        });
        this.updateLists();
    }

    addServiceToList(element, list) {
        return this[list].push(element.val());
    }

    removeServiceFromList(element, list) {
        for (var i = 0; i < this[list].length; i++)
            if (this[list][i] === element.val()) {
                return this[list].splice(i, 1);
            }
    }

    cleanLists() {
        this.serviceList = this.$main.find('.checkboxselect input:checkbox').map(function () {
            return $(this).val();
        }).toArray();
        this.serviceListSelected = [];
        this.updateLists();
    }

    calculateSlotCount() {
        var slotCount = 1;
        var selectedSlots = this.$main.find('.checkboxdeselect label:visible input:checkbox').map(function () {
            return $(this).data('slots');
        }).toArray();
        for (var i = 1; i < selectedSlots.length; i++)
            if (selectedSlots[i] > 0) {
                slotCount += selectedSlots[i];
            }
        if (this.slotsRequired > slotCount) {
            slotCount = this.slotsRequired;
        }
        $('#appointmentForm_slotCount').val(slotCount).trigger('change');
    }
}

export default View;

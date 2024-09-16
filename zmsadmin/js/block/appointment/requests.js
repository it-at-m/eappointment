import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor(element, options) {
        super(element, options);
        this.$main = $(element);
        this.selectedProcess = options.selectedProcess;
        this.selectedTime = options.selectedTime;
        this.slotsRequired = options.slotsRequired;
        this.serviceList = []
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
    updateLists(triggered = false) {
        this.$main.find('.checkboxdeselect input:checkbox').each((index, element) => {
            if ($.inArray($(element).val(), this.serviceListSelected) !== -1) {
                $(element).prop("checked", true);
               // $(element).closest('li').attr("aria-checked", true);
                $(element).closest('li').show();
            } else {
                $(element).prop("checked", false);
                //$(element).closest('li').attr("aria-checked", false);
                $(element).closest('li').hide();    
            }
        });

        this.$main.find('.checkboxselect input:checkbox').each((index, element) => {
            if ($.inArray($(element).val(), this.serviceList) !== -1) {
                $(element).prop("checked", false);
                //$(element).closest('li').attr("aria-checked", false);
                $(element).closest('li').show();
            } else {
                //$(element).closest('li').attr("aria-checked", true);
                $(element).closest('li').hide();
            }
            
        });
        if (triggered) {
            this.calculateSlotCount();
        }
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
        this.updateLists(true);
    }

    calculateSlotCount() {
        let slotCount = 1;
        var requestList = this.$main.find('.checkboxdeselect label:visible input:checkbox').map(function () {
            return $(this).data().slots;
        }).toArray();

        if (requestList.length > 0) {
            slotCount = requestList.reduce((partial_sum, count) => {
                return partial_sum + count;
            });
        }
        slotCount = slotCount == 0 ? 1 : slotCount;
        $('#appointmentForm_slotCount').val(Math.ceil(slotCount)).trigger('change');
        if (slotCount > $('#appointmentForm_slotCount option:last').val()) {
            $('#exceeded-slot-count').show()
        }else {
            $('#exceeded-slot-count').hide()
        }
    }
}

export default View;

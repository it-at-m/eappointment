import BaseView from "../../lib/baseview"
import $ from "jquery"
import moment from 'moment'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.selectedProcess = options.selectedProcess;
        this.serviceList = [];
        this.serviceListSelected = [];
        this.slotCount = 0;
        console.log('Component: RequestList actions', this, options);
    }

    loadList () {
        if (this.selectedProcess)
            this.readList()
        else
            this.cleanLists();
    }

    /**
     * update events after replacing list
     */
    updateLists () {
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

    readList ()
    {
        this.$main.find('.checkboxselect input:checked').each((index, element) => {
            if ($.inArray($(element).val(), this.serviceListSelected) === -1)
                this.addServiceToList ($(element), this.serviceListSelected)
        });
        this.$main.find('.checkboxdeselect input:not(:checked)').each((index, element) => {
            if ($.inArray($(element).val(), this.serviceList) === -1)
                this.addServiceToList ($(element), this.serviceList)
        });
        this.updateLists();
    }

    addServiceToList (element, list) {
        return list.push(element.val());
    }

    removeServiceFromList (element, list) {
        for (var i = 0; i < list.length; i++)
            if (list[i] === element.val()) {
                return list.splice(i,1);
            }
    }

    cleanLists ()
    {
        this.serviceList = this.$main.find('.checkboxselect input:checkbox').map(function() {
            return $(this).val();
        }).toArray();
        this.serviceListSelected = [];
    }

    calculateSlotCount () {
        var slotCount = 0;
        var selectedSlots = this.$main.find('.checkboxdeselect label:visible input:checkbox').map(function() {
            return $(this).data('slots');
        }).toArray();
        for (var i = 1; i < selectedSlots.length; i++)
            if (selectedSlots[i] > 0) {
                slotCount += selectedSlots[i];
            }
        this.slotCount = slotCount;
        this.$main.find('#appointmentForm_slotCount option:eq(' + this.slotCount +')').prop('selected', true)
    }
}

export default View;

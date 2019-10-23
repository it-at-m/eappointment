import RequestView from "./requests"
import FreeProcessView from './free-process-list'
import FormButtons from './form-buttons'
import $ from "jquery"
import moment from 'moment'
import maxChars from '../../element/form/maxChars'

class View extends RequestView {

    constructor(element, options) {
        super(element, options);
        this.$main = $(element);
        this.options = options;
        this.setOptions();
        this.setCallbacks();
        this.bindPublicMethods('load', 'bindEvents');
        $.ajaxSetup({ cache: false });
        if (!this.constructOnly) {
            this.load();
        } else {
            this.loadPartials();
        }

        $('textarea.maxchars').each(function () { maxChars(this) });
        this.$main.find('[name="familyName"]').focus();
        //console.log('Component: AppointmentView', this, options);
    }

    setOptions() {
        this.selectedDate = this.options.selectedDate;
        this.selectedTime = this.options.selectedTime;
        this.includeUrl = this.options.includeUrl || "";
        this.showLoader = this.options.showLoader || false;
        this.selectedProcess = this.options.selectedProcess;
        this.selectedScope = this.options.selectedScope;
        this.slotsRequired = this.options.slotsRequired;
        this.slotType = this.options.slotType;
        this.constructOnly = this.options.constructOnly;
    }

    setCallbacks() {
        this.onConfirm = this.options.onConfirm;
        this.onChangeScope = this.options.onChangeScope;
        this.onAbortProcess = this.options.onAbortProcess;
        this.onCancelForm = this.options.onCancelAppointmentForm;
        this.onDeleteProcess = this.options.onDeleteProcess;
        this.onSaveProcess = this.options.onSaveProcess;
        this.onReserveProcess = this.options.onReserveProcess;
        this.onEditProcess = this.options.onEditProcess;
        this.onCopyProcess = this.options.onCopyProcess;
        this.onQueueProcess = this.options.onQueueProcess;
        this.onDatePick = this.options.onDatePick;
        this.onAbortMessage = this.options.onAbortMessage;
        this.onPrintWaitingNumber = this.options.onPrintWaitingNumber;
        this.onSelectDateWithOverlay = this.options.onSelectDateWithOverlay;
        this.onChangeSlotCountCallback = this.options.onChangeSlotCount;
    }

    load() {
        const url = `${this.includeUrl}/appointmentForm/?selecteddate=${this.selectedDate}&selectedtime=${this.selectedTime}&selectedprocess=${this.selectedProcess}&selectedscope=${this.selectedScope}`
        return this.loadContent(url, 'GET', null, null, this.showLoader)
            .then(() => {
                this.assigneMainFormValues();
                this.loadPromise.then(() => {
                    this.initRequestView();
                    this.bindEvents();
                    this.$main.find('select#process_time').trigger('change');
                });
            });
    }

    loadPartials() {
        this.assigneMainFormValues();
        this.loadPromise.then(() => {
            this.initRequestView(true);
            this.bindEvents();
            this.$main.find('select#process_time').trigger('change');
        }).then(() => {
            this.loadFreeProcessList().loadList().then(() => {
                this.bindEvents();
            });
        });
    }

    assigneMainFormValues() {
        this.$main.find('.add-date-picker input#process_date').val(moment(this.selectedDate, 'YYYY-MM-DD').format('DD.MM.YYYY'));
        this.$main.find('input#process_selected_date').val(moment(this.selectedDate, 'YYYY-MM-DD').format('YYYY-MM-DD'));
        this.$main.find('[name="familyName"]').focus();
        this.$main.find('textarea.maxchars').each(function () {
            maxChars(this);
        })
        this.$main.find('[name="familyName"]').focus();
    }

    loadFreeProcessList() {
        return new FreeProcessView(this.$main.find('[data-free-process-list]'), {
            includeUrl: this.includeUrl,
            selectedDate: this.selectedDate,
            selectedTime: this.selectedTime,
            slotsRequired: this.slotsRequired,
            slotType: this.slotType,
            selectedScope: this.selectedScope,
            selectedProcess: this.selectedProcess
        });
    }

    bindEvents() {
        this.$main.off().on('change', '.checkboxselect input:checkbox', (event) => {
            this.onAddRequest(event);
        }).on('change', '.checkboxdeselect input:checkbox', (event) => {
            this.onRemoveRequest(event);
        }).on('click', '.clear-list', () => {
            this.onClearRequestList();
        }).on('change', '#appointmentForm_slotCount', (event) => {
            this.onChangeSlotCount(event);
        }).on('click', '.add-date-picker input', (event) => {
            this.onSelectDateWithOverlay(event);
        }).on('keydown', '.add-date-picker input', (event) => {
            var key = event.keyCode || event.which;
            switch(key) {
            case 13: // ENTER    
                this.onSelectDateWithOverlay(event);
                break;
            }
        }).on('change', '.appointment-form .switchcluster select', (event) => {
            this.onChangeScope(event);
        }).on('change', 'select#process_time', (event) => {
            this.onChangeProcessTime(event);
        }).on('click', '.form-actions button.process-reserve', (event) => {
            this.onReserveProcess(this, event);
        }).on('click', '.form-actions button.process-save', (event) => {
            this.onSaveProcess(this, event);
        }).on('click', '.form-actions button.process-print', (event) => {
            this.onPrintWaitingNumber(event);
        }).on('click', '.form-actions button.process-queue', (event) => {
            this.onQueueProcess(this, event);
        }).on('click', '.form-actions button.process-copy', (event) => {
            this.onCopyProcess(this, event);
        }).on('click', '.form-actions button.process-delete', (event) => {
            this.onConfirm(event, "confirm_delete", () => { this.onDeleteProcess(event) });
        }).on('click', '.form-actions button.process-abort', (event) => {
            this.onAbortProcess(this.$main, event);
        }).on('click', '.form-actions .button-cancel', (event) => {
            this.onCancelForm(event);
        });
    }

    onClearRequestList() {
        this.cleanLists();
    }

    onAddRequest(event) {
        this.addServiceToList($(event.currentTarget), 'serviceListSelected');
        this.removeServiceFromList($(event.currentTarget), 'serviceList');
        this.updateLists();
    }

    onRemoveRequest(event) {
        this.removeServiceFromList($(event.currentTarget), 'serviceListSelected');
        this.addServiceToList($(event.currentTarget), 'serviceList');
        this.updateLists();
    }

    onChangeSlotCount(event) {
        // if human event, not triggered
        //if (event.originalEvent !== undefined) {
        this.slotsRequired = $(event.currentTarget).val();
        //}
        this.loadFreeProcessList().loadList().then(() => {
            this.bindEvents();
        });
        this.onChangeSlotCountCallback(event);
    }

    onChangeProcessTime(event) {
        this.selectedTime = $(event.currentTarget).val();
        var hasFreeAppointments = (1 <= $(event.currentTarget).length && '00-00' != this.selectedTime);
        this.$main.data('selected-time', this.selectedTime);
        new FormButtons(this.$main.find('[data-form-buttons]'), {
            includeUrl: this.includeUrl,
            selectedDate: this.selectedDate,
            selectedProcess: this.selectedProcess,
            hasFreeAppointments: hasFreeAppointments,
            selectedTime: this.selectedTime
        }).loadButtons().then(() => {
            this.bindEvents();
        });
    }
}

export default View;

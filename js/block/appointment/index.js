import RequestView from "./requests"
import FreeProcessView from './free-process-list'
import FormButtons from './form-buttons'
import $ from "jquery"
import maxChars from '../../element/form/maxChars'
import moment from 'moment'

class View extends RequestView {

    constructor (element, options) {
        super(element, options);
        this.$main = $(element);
        this.options = options;
        this.setOptions();
        this.setCallbacks();
        $.ajaxSetup({ cache: false });
        if (! this.constructOnly) {
          this.load();
        } else {
          this.loadPartials();
        }

        $('textarea.maxchars').each(function() {maxChars(this)});
        this.$main.find('[name="familyName"]').focus();
    }

    setOptions()
    {
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

    setCallbacks()
    {
        this.onChangeScope = this.options.onChangeScope;
        this.onAbortProcess = this.options.onAbortProcess;
        this.onDeleteProcess = this.options.onDeleteProcess;
        this.onSaveProcess = this.options.onSaveProcess;
        this.onEditProcess = this.options.onEditProcess;
        this.onQueueProcess = this.options.onQueueProcess;
        this.onDatePick = this.options.onDatePick;
        this.onAbortMessage = this.options.onAbortMessage;
        this.onPrintWaitingNumber = this.options.onPrintWaitingNumber;
        this.onSelectDateWithOverlay = this.options.onSelectDateWithOverlay;
        this.onChangeSlotCountCallback = this.options.onChangeSlotCount;
    }

    load() {
        const url = `${this.includeUrl}/appointmentForm/?selecteddate=${this.selectedDate}&selectedprocess=${this.selectedProcess}&selectedscope=${this.selectedScope}`
        return this.loadContent(url, 'GET', null, null, this.showLoader).then(() => {
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
            this.loadFreeProcessList().loadList().then(() => {
                this.bindEvents();
                this.selectedFreeProcessTime = this.$main.find('[data-free-process-list] option').val();
                this.$main.find('select#process_time').trigger('change');
            });
        });
    }

    assigneMainFormValues()
    {
        this.$main.find('.add-date-picker input#process_date').val(moment(this.selectedDate, 'YYYY-MM-DD').format('DD.MM.YYYY'));
        this.$main.find('input#process_selected_date').val(moment(this.selectedDate, 'YYYY-MM-DD').format('YYYY-MM-DD'));
        //this.$main.find('.appointment-form .switchcluster select').val(this.selectedScope);
        this.$main.find('[name="familyName"]').focus();
    }

    loadFormButtons() {
        return new FormButtons(this.$main.find('[data-form-buttons]'), {
            includeUrl: this.includeUrl,
            selectedDate: this.selectedDate,
            selectedFreeProcessTime: this.selectedFreeProcessTime,
            selectedProcess: this.selectedProcess
        });
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
        }).on('click', '.add-date-picker', () => {
            this.onSelectDateWithOverlay();
        }).on('focus', '.add-date-picker', () => {
            this.onSelectDateWithOverlay();
        }).on('change', '.appointment-form .switchcluster select', (event) => {
            this.onChangeScope(event);
        }).on('change', 'select#process_time', (event) => {
            this.onChangeProcessTime(event);
        }).on('click', '.form-actions button.process-reserve', (event) => {
            this.onSaveProcess(this.$main, event, 'reserve');
        }).on('click', '.form-actions button.process-save', (event) => {
            this.onSaveProcess(this.$main, event);
        }).on('click', '.form-actions button.process-queue', (event) => {
            this.onQueueProcess(this.$main, event);
        }).on('click', '.form-actions button.process-copy', (event) => {
            this.onSaveProcess(this.$main, event, 'reserve');
        }).on('click', '.form-actions button.process-delete', (event) => {
            this.onDeleteProcess(this.$main, event)
        }).on('click', '.form-actions button.process-abort', (event) => {
            this.onAbortProcess(this.$main, event);
        }).on('click', '[data-action-abort]', (event) => {
            this.onAbortMessage(event);
        }).on('click', '[data-action-printWaitingNumber]', (event) => {
            this.onPrintWaitingNumber(event);
        });
    }

    onClearRequestList() {
        this.cleanLists();
    }

    onAddRequest(event) {
        this.addServiceToList($(event.target), 'serviceListSelected');
        this.removeServiceFromList($(event.target), 'serviceList');
        this.updateLists();
    }

    onRemoveRequest(event) {
        this.removeServiceFromList($(event.target), 'serviceListSelected');
        this.addServiceToList($(event.target), 'serviceList');
        this.updateLists();
    }

    onChangeSlotCount(event) {
        this.slotsRequired = $(event.target).val();
        this.loadFreeProcessList().loadList().then(() => {
            this.bindEvents();
            this.$main.find('select#process_time').trigger('change');
        });
        this.onChangeSlotCountCallback(event);
    }

    onChangeProcessTime(event) {
        this.selectedFreeProcessTime = $(event.target).val();
        this.loadFormButtons().loadButtons().then(() => {
            this.bindEvents();
        });
    }
}

export default View;

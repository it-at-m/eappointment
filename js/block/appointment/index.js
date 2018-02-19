import BaseView from "../../lib/baseview"
import $ from "jquery"
import FreeProcessList from './free-process-list'
import FormButtons from './form-buttons'
import RequestList from "./requests"
import maxChars from '../../element/form/maxChars'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.$main = $(element);
        this.options = options;
        this.setOptions();
        this.setCallbacks();
        this.FormButtons = new FormButtons(element, this.options);
        this.RequestList = new RequestList(element, this.options);
        $.ajaxSetup({ cache: false });

        if (! options.constructOnly) {
          this.load().then(() => {
              this.loadAppointmentFormParts();
          });
        } else {
          this.loadAppointmentFormParts();
        }
    }

    setOptions()
    {
        this.selectedDate = this.options.selectedDate;
        this.selectedTime = this.options.selectedTime;
        this.includeUrl = this.options.includeUrl || "";
        this.showLoader = this.options.showLoader || false;
        this.selectedProcess = this.options.selectedProcess;
        this.selectedScope = this.options.selectedScope;
    }

    setCallbacks()
    {
        this.onChangeScope = this.options.onChangeScope || (() => {});
        this.onAbortProcess = this.options.onAbortProcess || (() => {});
        this.onDeleteProcess = this.options.onDeleteProcess || (() => {});
        this.onSaveProcess = this.options.onSaveProcess || (() => {});
        this.onEditProcess = this.options.onEditProcess || (() => {});
        this.onQueueProcess = this.options.onQueueProcess || (() => {});
        this.onDatePick = this.options.onDatePick || (() => {});
        this.onAbortMessage = this.options.onAbortMessage || (() => {});
        this.onPrintWaitingNumber = this.options.onPrintWaitingNumber || (() => {});
        this.onSelectDateWithOverlay = this.options.onSelectDateWithOverlay || (() => {});
    }

    load() {
        const url = `${this.includeUrl}/appointmentForm/?selecteddate=${this.selectedDate}&selectedprocess=${this.selectedProcess}&selectedscope=${this.selectedScope}`
        this.loadPromise = this.loadContent(url, 'GET', null, null, this.showLoader).catch(err => this.loadErrorCallback(err));
        return this.loadPromise;
    }

    loadAppointmentFormParts() {
        this.FreeProcessList = new FreeProcessList(this.$main.find('[data-free-process-list]'), this.options);
        this.FreeProcessList.loadList().then(() => {
            this.RequestList.loadList();
            this.FormButtons.load();
            this.bindEvents();
            this.bindButtonEvents();
        });
        $('textarea.maxchars').each(function() {
            maxChars(this);
        });
        this.$main.find('[name="familyName"]').focus();
    }

    bindEvents() {
        this.$main.off().on('change', '.checkboxselect input:checkbox', (event) => {
            this.RequestList.addServiceToList($(event.target), this.RequestList.serviceListSelected);
            this.RequestList.removeServiceFromList($(event.target), this.RequestList.serviceList);
            this.RequestList.updateLists();
            this.FreeProcessList.loadList();
        }).on('change', '.checkboxdeselect input:checkbox', (event) => {
            this.RequestList.removeServiceFromList($(event.target), this.RequestList.serviceListSelected);
            this.RequestList.addServiceToList($(event.target), this.RequestList.serviceList);
            this.RequestList.updateLists();
            this.FreeProcessList.loadList();
        }).on('click', '.clear-list', () => {
            this.RequestList.cleanLists();
            this.RequestList.updateLists();
            this.FreeProcessList.loadList();
        }).on('click', '.add-date-picker', () => {
            this.onSelectDateWithOverlay();
        }).on('change', 'select#appointmentForm_slotCount', () => {
            console.log('slots changed manualy');
            this.RequestList.slotCount = this.$main.find('select#appointmentForm_slotCount').val();
            this.FreeProcessList.loadList();
        }).on('change', '.appointment-form .switchcluster select', (event) => {
            this.onChangeScope(event.target.value);
        })
    }

    bindButtonEvents() {
        this.$main.on('click', '.form-actions button.process-reserve', (event) => {
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
        })
    }


}

export default View;

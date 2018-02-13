import BaseView from "../../lib/baseview"
import $ from "jquery"
import FreeProcessList from './free-process-list'
import FormButtons from './form-buttons'
import ActionHandler from "./action"
import RequestList from "./requests"
import maxChars from '../../element/form/maxChars'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.$main = $(element);
        this.options = options;
        this.selectedDate = options.selectedDate;
        this.selectedTime = options.selectedTime;
        this.includeUrl = options.includeUrl || "";
        this.showLoader = options.showLoader || false;
        this.selectedProcess = options.selectedProcess;
        this.selectedScope = options.selectedScope;
        this.onChangeScope = options.onChangeScope || (() => {});
        this.onAbortProcess = options.onAbortProcess || (() => {});
        this.onDeleteProcess = options.onDeleteProcess || (() => {});
        this.onSaveProcess = options.onSaveProcess || (() => {});
        this.onEditProcess = options.onEditProcess || (() => {});
        this.onQueueProcess = options.onQueueProcess || (() => {});
        this.onDatePick = options.onDatePick || (() => {});
        this.ActionHandler = new ActionHandler(element, options);
        this.FormButtons = new FormButtons(this.$main, this.options);
        this.RequestList = new RequestList(this.$main, this.options);
        $.ajaxSetup({ cache: false });

        if (! options.constructOnly) {
          this.load().then(() => {
              this.loadAppointmentFormParts();
          });
        } else {
          this.loadAppointmentFormParts();
        }
    }

    loadAppointmentFormParts() {
        this.FreeProcessList = new FreeProcessList(this.$main.find('[data-free-process-list]'), this.options);
        this.FreeProcessList.loadList().then(() => {
            this.RequestList.loadList();
            this.FormButtons.load();
            this.bindEvents();
        });
        $('textarea.maxchars').each(function() {
            maxChars(this);
        });
        this.$main.find('[name="familyName"]').focus();
    }

    load() {
        const url = `${this.includeUrl}/appointmentForm/?selecteddate=${this.selectedDate}&selectedprocess=${this.selectedProcess}&selectedscope=${this.selectedScope}`
        this.loadPromise = this.loadContent(url, 'GET', null, null, this.showLoader).catch(err => this.loadErrorCallback(err));
        return this.loadPromise;
    }

    loadByCallbackUrl(url) {
        this.loadPromise = this.loadContent(url).catch(err => this.loadErrorCallback(err));
        return this.loadPromise;
    }

    loadNew () {
        const url = `${this.includeUrl}/appointmentForm/?selectedprocess=${this.selectedProcess}&new=1&selecteddate=${this.selectedDate}&selectedscope=${this.selectedScope}`
        this.loadPromise = this.loadContent(url).then(() => {
            this.RequestList.loadList();
            this.FreeProcessList.loadList();
            this.FormButtons.load();
            this.bindEvents();
        }).catch(err => this.loadErrorCallback(err));
        return this.loadPromise;
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
            this.ActionHandler.selectDateWithOverlay();
        }).on('change', 'select#appointmentForm_slotCount', () => {
            console.log('slots changed manualy');
            this.RequestList.slotCount = this.$main.find('select#appointmentForm_slotCount').val();
            this.FreeProcessList.loadList();
        }).on('click', '.form-actions button.process-reserve', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.ActionHandler.reserve(event).then((response) => {
                this.loadMessage(response, this.onSaveProcess)
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '.form-actions button.process-queue', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.ActionHandler.queue(event).then((response) => {
                this.loadMessage(response, this.onQueueProcess);
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '.form-actions button.process-new', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.loadNew();
        }).on('click', '.form-actions button.process-edit', (event) => {
            this.onEditProcess($(event.target).data('id'))
        }).on('click', '.form-actions button.process-delete', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.ActionHandler.delete(event).then((response) => {
                this.loadMessage(response, this.onDeleteProcess);
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '.form-actions button.process-abort', (event) => {
            this.onAbortProcess();
        }).on('click', '.form-actions button.process-save', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.ActionHandler.save(event).then((response) => {
                this.loadMessage(response, this.onSaveProcess);
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '[data-button-print]', (event) => {
            event.preventDefault()
            event.stopPropagation()
            this.ActionHandler.printWaitingNumber();
        }).on('change', '.appointment-form .switchcluster select', (event) => {
            this.onChangeScope(event.target.value);
        })
    }


}

export default View;

import React from 'react';
import { createRoot } from 'react-dom/client';
import RequestView from "./requests"
import FreeProcessView from './free-process-list'
import FormButtons from './form-buttons'
import $ from "jquery"
import maxChars from '../../element/form/maxChars'
import Datepicker from '../../lib/inputs/date'
import { getDataAttributes } from '../../lib/utils'
import moment from 'moment'

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
        //this.$main.find('[name="familyName"]').focus(); // -> nicht barrierefrei
        //console.log('Component: AppointmentView', this, options);
        this.hasSlotCountEnabled = this.$main.find('#appointmentForm_slotCount').length;
    }

    setOptions() {
        this.selectedDate = this.options.selectedDate;
        this.selectedTime = this.options.selectedTime;
        this.includeUrl = this.options.includeUrl || "";
        this.showLoader = this.options.showLoader || false;
        this.selectedProcess = this.options.selectedProcess;
        this.selectedScope = this.options.selectedScope;
        this.clusterEnabled = this.options.clusterEnabled || false;
        this.slotsRequired = this.options.slotsRequired;
        this.slotType = this.options.slotType;
        this.constructOnly = this.options.constructOnly;
    }

    setCallbacks() {
        this.onConfirm = this.options.onConfirm;
        this.onChangeScopeCallback = this.options.onChangeScope;
        this.onAbortProcess = this.options.onAbortProcess;
        this.onCancelForm = this.options.onCancelAppointmentForm;
        this.onDeleteProcess = this.options.onDeleteProcess;
        this.onSaveProcess = this.options.onSaveProcess;
        this.onChangeProcess = this.options.onChangeProcess;
        this.onReserveProcess = this.options.onReserveProcess;
        this.onEditProcess = this.options.onEditProcess;
        this.onCopyProcess = this.options.onCopyProcess;
        this.onQueueProcess = this.options.onQueueProcess;
        this.onDatePick = this.options.onDatePick;
        this.onAbortMessage = this.options.onAbortMessage;
        this.onPrintWaitingNumber = this.options.onPrintWaitingNumber;
        this.onPrintProcessMail = this.options.onPrintProcessMail;
        this.onChangeSlotCountCallback = this.options.onChangeSlotCount;
    }

    load() {
        const url = `${this.includeUrl}/appointmentForm/?selecteddate=${this.selectedDate}&selectedtime=${this.selectedTime}&selectedprocess=${this.selectedProcess}&selectedscope=${this.selectedScope}`
        return this.loadContent(url, 'GET', null, null, this.showLoader)
            .then(() => {
                this.auralMessages = getDataAttributes($('#auralmessage').get('0')).aural
                this.assigneMainFormValues();
                this.loadPromise.then(() => {
                    this.initRequestView();
                    this.bindEvents();
                    this.$main.find('select#process_time').trigger('change');
                    this.loadDatePicker();
                });
            });
    }

    loadPartials() {
        this.auralMessages = getDataAttributes($('#auralmessage').get('0')).aural
        this.assigneMainFormValues();
        this.loadPromise.then(() => {
            this.initRequestView(true);
            this.bindEvents();
            this.$main.find('select#process_time').trigger('change');
        }).then(() => {
            if (this.selectedScope || this.selectedDate) {
                this.loadDatePicker();
                this.loadFreeProcessList().loadList().then(() => {
                    this.bindEvents();
                });
            }
        });
    }

    loadDatePicker() {
        const calendarElement = createRoot(document.getElementById('appointment-datepicker'));
        const onChangeDate = (value) => {
            if (this.hasSlotCountEnabled && this.serviceListSelected.length == 0)
                this.auralMessage(this.auralMessages.chooseRequestFirst)
            this.onDatePick(value)
        }
        return (
            calendarElement.render(
                <Datepicker
                    id="process_date"
                    accessKey="m"
                    value={new Date(this.selectedDate).getTime() / 1000}
                    onChange={onChangeDate}
                />
            )
        );
    }

    assigneMainFormValues() {
        this.$main.find('.add-date-picker input#process_date').val(moment(this.selectedDate, 'YYYY-MM-DD').format('DD.MM.YYYY'));
        this.$main.find('input#process_selected_date').val(moment(this.selectedDate, 'YYYY-MM-DD').format('YYYY-MM-DD'));
        this.$main.find('textarea.maxchars').each(function () {
            maxChars(this);
        })
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
        }).on('change', '.appointment-form .switchcluster select', (event) => {
            this.onChangeScope(event);
        }).on('change', 'select#process_time', (event) => {
            this.onChangeProcessTime(event);
        }).on('click', '.form-actions button.process-reserve', (event) => {
            this.onReserveProcess(this, event);
        }).on('click', '.form-actions button.process-change', (event) => {
            this.onChangeProcess(this, event);
        }).on('click', '.form-actions button.process-save', (event) => {
            this.onSaveProcess(this, event);
        }).on('click', '.form-actions button.process-edit', (event) => {
            this.onEditProcess(this, event);
        }).on('click', '.form-actions button.process-print-mail', (event) => {
            this.onPrintProcessMail(event);
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
        this.auralMessage(this.auralMessages.clean);
    }

    onAddRequest(event) {
        this.addServiceToList($(event.currentTarget), 'serviceListSelected');
        this.removeServiceFromList($(event.currentTarget), 'serviceList');
        this.updateLists(true);
        this.auralMessage(this.auralMessages.add + ': ' + $(event.currentTarget).parent().find('span').text());
    }

    onRemoveRequest(event) {
        this.removeServiceFromList($(event.currentTarget), 'serviceListSelected');
        this.addServiceToList($(event.currentTarget), 'serviceList');
        this.updateLists(true);
        this.auralMessage(this.auralMessages.remove + ': ' + $(event.currentTarget).parent().find('span').text());
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

    onChangeScope(event) {
        this.selectedScope = $(event.currentTarget).val();
        this.onChangeScopeCallback(event);

    }

    onChangeProcessTime(event) {
        if (this.hasSlotCountEnabled && this.serviceListSelected.length == 0)
                this.auralMessage(this.auralMessages.chooseRequestFirst)
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

        this.$.find('input[name=sendMailConfirmation]').prop('checked', hasFreeAppointments)
    }

    auralMessage(message) {
        let infoNode = document.createTextNode(message);
        let paragraph = document.createElement('p');
        paragraph.appendChild(infoNode);

        let messageContainer = this.$.find('#auralmessage');
        messageContainer.find('p').remove();
        messageContainer.append(paragraph);
    }
}

export default View;

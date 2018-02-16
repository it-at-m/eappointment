/* global window */
import BaseView from '../../lib/baseview'
import $ from 'jquery'
import settings from '../../settings'
import AppointmentView from '../../block/appointment'
import QueueView from '../../block/queue'
import CalendarView from '../../block/calendar'
import ClientNextView from '../../block/process/next'
import ActionHandler from "../../block/appointment/action"

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element).focus();
        this.includeUrl = options.includeurl;
        this.selectedDate = options['selected-date'];
        this.selectedTime = options['selected-time'];
        this.selectedProcess = options['selected-process'];
        this.selectedScope=0;
        this.calledProcess = options['called-process'];
        this.ActionHandler = (new ActionHandler(this.$main.find('[data-appointment-form]'), options));
        this.slotType = 'intern';
        this.slotsRequired = 0;
        this.reloadTimer;
        this.lastReload = 0;
        this.initiator = 'Sachbearbeiter';
        this.bindPublicMethods(
            'loadAllPartials',
            'onAbortMessage',
            'onAbortProcess',
            'onChangeScope',
            'onPrintWaitingNumber',
            'onDatePick',
            'onDateToday',
            'onNextProcess',
            'onDeleteProcess',
            'onEditProcess',
            'onSaveProcess',
            'onQueueProcess',
            'onResetProcess',
            'onSendCustomMail'
        );
        this.$.ready(() => {
            this.loadData;
            this.setLastReload();
            this.setReloadTimer();
        });
        $.ajaxSetup({ cache: false });
        this.loadAllPartials().then(() => this.bindEvents());
        console.log('Component: Workstation', this, options);
    }

    bindEvents() {
        window.onfocus = () => {
            //console.log("on Focus");
            if (this.lastReload > settings.reloadInterval) {
                this.loadReloadPartials();
                this.lastReload = 0;
            }
            this.setReloadTimer();
        }
        window.onblur = () => {
            //console.log("lost Focus");
            clearTimeout(this.reloadTimer);
        }
        this.$main.find('[data-queue-table]').mouseenter(() => {
            //console.log("stop Reload on mouse enter");
            clearTimeout(this.reloadTimer);
        });
        this.$main.find('[data-queue-table]').mouseleave(() => {
            //console.log("start reload on mouse leave");
            this.setReloadTimer();
        });
    }

    setReloadTimer() {
        clearTimeout(this.reloadTimer);
        this.reloadTimer = setTimeout(() => {
            this.lastReload = 0;
            this.loadReloadPartials();
            this.bindEvents();
            this.setReloadTimer();
        }, settings.reloadInterval * 1000);
    }

    setLastReload() {
        setTimeout(() => {
            this.lastReload++;
            //console.log(this.lastReload);
            this.setLastReload();
        }, 1000);
    }

    onDatePick(date, full = false) {
        this.selectedDate = date;
        this.$main.data('selected-date', date);
        if (full) {
            this.loadAllPartials();
        } else {
            this.loadCalendar();
            this.loadClientNext();
            this.loadQueueTable();
            this.ActionHandler.setSelectedDate(date);
            this.loadAppointmentForm(true, true);
            this.$main.find('.appointment-form .switchcluster select').val(this.selectedScope);
            this.$main.find('[name="familyName"]').focus();
        }
    }

    onChangeScope(scopeId) {
        this.selectedScope = scopeId;
        this.loadCalendar();
        this.loadAppointmentForm(true, true);
        //this.$main.find('.appointment-form .switchcluster select').val(this.selectedScope);
    }

    onDateToday(date, full = false) {
        this.onDatePick(date, full);
    }

    onSaveProcess ($container, event, action = 'update') {
        this.ActionHandler.stopEvent(event);
        this.selectedProcess = $(event.target).data('id');
        const sendData = $container.find('form').serializeArray();
        sendData.push({name: action, value: 1});
        sendData.push({name: 'selectedprocess', value: this.selectedProcess});
        sendData.push({name: 'initiator', value: this.initiator});
        this.loadContent(`${this.includeUrl}/appointmentForm/`, 'POST', sendData, $container).then(() => {
            this.loadAppointmentForm(true, true, $container);
            this.loadQueueTable();
            this.loadCalendar();
        });
    }

    onAbortProcess($container, event) {
        this.ActionHandler.stopEvent(event);
        this.selectedProcess = null;
        this.loadAppointmentForm(true, false, $container);
    }

    onAbortMessage(event)
    {
        this.ActionHandler.stopEvent(event);
        $(event.target).closest('.message').fadeOut().remove();
    }

    onDeleteProcess ($container, event, confirm = false) {
        this.ActionHandler.stopEvent(event);
        this.selectedProcess = null;
        if (confirm) {
            this.onConfirmDeleteProcess(event);
        } else {
            const processId  = $(event.target).data('id');
            this.loadContent(`${this.includeUrl}/appointmentForm/`, 'POST', {'delete': 1, 'processId': processId, 'initiator': this.initiator}, $container).then(() => {
                this.loadAppointmentForm(true, true, $container);
                this.loadQueueTable();
                this.loadCalendar();
            });
        }

    }

    onConfirmDeleteProcess(event)
    {
      const processId  = $(event.target).data('id');
      const name  = $(event.target).data('name');
      this.loadCall(`${this.includeUrl}/dialog/?template=confirm_delete&parameter[id]=${processId}&parameter[name]=${name}`).then((response) => {
          this.loadDialog(response, (() => {
              this.loadCall(`${this.includeUrl}/appointmentForm/`, 'POST', {'delete': 1, 'processId': processId, 'initiator': this.initiator}).then(() => {
                  this.loadQueueTable();
                  this.loadCalendar();
              });
          }));
      });
    }

    onQueueProcess ($container, event) {
        this.ActionHandler.stopEvent(event);
        const sendData = $container.find('form').serializeArray();
        sendData.push({name: 'queue', value: 1});
        this.loadContent(`${this.includeUrl}/appointmentForm/`, 'POST', sendData, $container).then(() => {
            this.loadAppointmentForm(true, true, $container);
            this.loadQueueTable();
            this.loadCalendar();
        });
    }

    onResetProcess ($container, event) {
        let selectedProcess = $(event.target).data('id');
        this.loadContent(`${this.includeUrl}/process/queue/reset/?selectedprocess=${selectedProcess}&selecteddate=${this.selectedDate}`, 'GET', null, $container);
    }

    onEditProcess (processId) {
        this.selectedProcess = processId;
        this.loadAppointmentForm();
    }

    onNextProcess() {
        this.calledProcess = null;
        this.loadQueueTable();
    }

    onPrintWaitingNumber (event) {
        this.ActionHandler.stopEvent(event);
        const processId  = $(event.target).data('id');
        $(event.target).closest('.message').fadeOut().remove();
        window.open(`${this.includeUrl}/process/queue/?print=1&selectedprocess=${processId}`)
    }

    onSendCustomMail ($container, event) {
        this.ActionHandler.stopEvent(event);
        const processId = $(event.target).data('process');
        const sendStatus = $(event.target).data('status');
        this.loadCall(`${this.includeUrl}/mail/?selectedprocess=${processId}&status=${sendStatus}&dialog=1`).then((response) => {
            this.loadDialog(response, (() => {this.loadQueueTable()}));
        });
    }

    loadAllPartials() {
        let promise = Promise.all([
            this.loadCalendar(),
            this.loadClientNext(),
            this.loadAppointmentForm(),
            this.loadQueueTable()
        ])
        return promise;
    }

    loadReloadPartials() {
        if (this.$main.find('.lightbox').length == 0)
            this.loadQueueTable(false);
    }

    loadCalendar (showLoader = true) {
        return new CalendarView($.find('[data-calendar]'), {
            selectedDate: this.selectedDate,
            selectedScope: this.selectedScope,
            selectedProcess: this.selectedProcess,
            slotsRequired: this.slotsRequired,
            slotType: this.slotType,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            includeUrl: this.includeUrl,
            onAbortMessage: this.onAbortMessage,
            showLoader: showLoader
        })
    }

    loadClientNext (showLoader = true) {
        return new ClientNextView($.find('[data-client-next]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            calledProcess: this.calledProcess,
            onNextProcess: this.onNextProcess,
            onAbortMessage: this.onAbortMessage,
            showLoader: showLoader
        })
    }

    loadAppointmentForm(showLoader = true, constructOnly = false, $container = null) {
        if (null === $container)
            $container = $.find('[data-appointment-form]');
        return new AppointmentView($container, {
            selectedDate: this.selectedDate,
            selectedTime: this.selectedTime,
            selectedProcess: this.selectedProcess,
            selectedScope: this.selectedScope,
            includeUrl: this.includeUrl,
            slotsRequired: this.slotsRequired,
            slotType: this.slotType,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            onDeleteProcess: this.onDeleteProcess,
            onEditProcess: this.onEditProcess,
            onQueueProcess: this.onQueueProcess,
            onSaveProcess: this.onSaveProcess,
            onChangeScope: this.onChangeScope,
            onAbortProcess: this.onAbortProcess,
            onPrintWaitingNumber: this.onPrintWaitingNumber,
            onAbortMessage: this.onAbortMessage,
            showLoader: showLoader,
            constructOnly: constructOnly
        })
    }

    loadQueueTable (showLoader = true) {
        return new QueueView($.find('[data-queue-table]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            onDeleteProcess: this.onDeleteProcess,
            onEditProcess: this.onEditProcess,
            onNextProcess: this.onNextProcess,
            onResetProcess: this.onResetProcess,
            onAbortMessage: this.onAbortMessage,
            onSendCustomMail: this.onSendCustomMail,
            showLoader: showLoader
        })
    }

}

export default View;

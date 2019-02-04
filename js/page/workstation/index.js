/* global window */
import BaseView from '../../lib/baseview'
import { stopEvent, showSpinner, hideSpinner } from '../../lib/utils'
import $ from 'jquery'
import settings from '../../settings'
import AppointmentView from '../../block/appointment'
import QueueView from '../../block/queue'
import CalendarView from '../../block/calendar'
import ClientNextView from '../../block/process/next'
import QueueInfoView from '../../block/queue/info'
import AppointmentTimesView from '../../block/appointment/times'


class View extends BaseView {
    constructor(element, options) {
        super(element);
        this.element = $(element).focus();
        this.includeUrl = options.includeurl;
        this.selectedTime = options['selected-time'];
        this.selectedDate = options['selected-date'];
        this.selectedProcess = options['selected-process'];
        this.selectedScope = options['selected-scope'] || 0;
        this.calledProcess = options['called-process'];
        this.slotType = 'intern';
        this.slotsRequired = 0;
        this.reloadTimer;
        this.lastReload = 0;
        this.initiator = 'Sachbearbeiter';
        this.bindPublicMethods(
            'loadAllPartials',
            'onAbortMessage',
            'onAbortProcess',
            'onCancelAppointmentForm',
            'onChangeScope',
            'onPrintWaitingNumber',
            'onDatePick',
            'onDateToday',
            'onSelectDateWithOverlay',
            'onNextProcess',
            'onCallNextProcess',
            'onCancelNextProcess',
            'onDeleteProcess',
            'onConfirm',
            'onEditProcess',
            'onSaveProcess',
            'onCopyProcess',
            'onQueueProcess',
            'onResetProcess',
            'onSendCustomMail',
            'onSendCustomNotification',
            'onSendNotificationReminder',
            'onReloadQueueTable',
            'onChangeTableView',
            'onChangeSlotCount',
            'onGhostWorkstationChange'
        );
        this.$.ready(() => {
            this.setLastReload();
            this.setReloadTimer();
        });
        $.ajaxSetup({ cache: false });
        this.loadAllPartials().then(() => {
            this.bindEvents();
        });
        //console.log('Component: Workstation', this, options);
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

    onDatePick($container, event) {
        stopEvent(event);
        this.selectedDate = $(event.target).attr('data-date');
        $container.removeClass('lightbox');
        $container.removeClass('lightbox__content');
        $container.data('selecteddate', this.selectedDate);
        this.loadCalendar();
        this.loadClientNext();
        this.loadQueueInfo();
        this.loadQueueTable();
        this.loadAppointmentForm(true, true);
    }

    onSelectDateWithOverlay() {
        const container = $.find('[data-calendar]');
        $(container).find('.calendar').addClass('lightbox__content');
        $(container).addClass('lightbox').on('click', () => {
            $(container).removeClass('lightbox');
            $(container).removeClass('lightbox__content');
        });
    }

    onChangeSlotCount(event) {
        this.slotsRequired = $(event.target).val();
        this.loadCalendar();
    }

    onChangeScope(event) {
        stopEvent(event);
        this.selectedScope = $(event.target).val();
        this.loadCalendar();
        this.loadAppointmentForm();
    }

    onChangeTableView($container, event, $element) {
        const pathName = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')).split('/').pop();
        $element.val(pathName);
        $(event.target).closest('form').submit();
    }

    onDateToday($container, event) {
        this.onDatePick($container, event)
    }

    onSaveProcess($container, event, action = 'update') {
        stopEvent(event);
        if ($(event.target).data('id')) {
            this.selectedProcess = $(event.target).data('id');
        }
        const sendData = $container.find('form').serializeArray();
        sendData.push({ name: action, value: 1 });
        sendData.push({ name: 'selectedprocess', value: this.selectedProcess });
        sendData.push({ name: 'initiator', value: this.initiator });
        this.loadContent(`${this.includeUrl}/appointmentForm/`, 'POST', sendData, $container).then((response) => {
            if ($(response).find('form').data('savedProcess')) {
                this.selectedProcess = $(response).find('form').data('savedProcess');
            }
            if (false === response.toLowerCase().includes('has-error')) {
                this.loadQueueInfo();
                this.loadQueueTable();
                this.loadCalendar();
                this.loadAppointmentForm(true, true);
            }
        });
    }

    onCopyProcess($container, event) {
        stopEvent(event);
        const sendData = $container.find('form').serializeArray();
        if (0 == $('select#process_time').find(':selected').data('free')) {
            var selectedTime = $('select#process_time').val();
            this.loadCall(`${this.includeUrl}/dialog/?template=copy_failed_time_unvalid&parameter[selectedtime]=${selectedTime}`).then((response) => {
                this.loadDialog(response, () => { this.onAbortProcess($container, event) });
            });
            return false;
        }
        sendData.push({ name: 'reserve', value: 1 });
        sendData.push({ name: 'initiator', value: this.initiator });
        this.loadContent(`${this.includeUrl}/appointmentForm/`, 'POST', sendData, $container).then((response) => {
            if (false === response.toLowerCase().includes('has-error')) {
                this.loadMessage(response, () => {
                    this.loadAppointmentForm(true, true);
                    this.loadQueueInfo();
                    this.loadQueueTable();
                    this.loadCalendar();
                }, $container);
            }
        });
    }

    onAbortProcess($container, event) {
        stopEvent(event);
        this.selectedProcess = null;
        this.loadAppointmentForm(true, false, $container);
    }

    onCancelAppointmentForm(event) {
        //console.log("Cancel Appointment Form")
        stopEvent(event);
        this.selectedProcess = null;
        this.selectedScope = null;
        this.loadAppointmentForm();
        this.loadCalendar();
    }

    onAbortMessage(event) {
        stopEvent(event);
        $(event.target).closest('.message').fadeOut().remove();
    }

    onDeleteProcess($container, event) {
        stopEvent(event);
        this.selectedProcess = null;
        const processId = $(event.target).data('id');
        showSpinner();
        this.loadCall(`${this.includeUrl}/appointmentForm/`, 'POST', { 'delete': 1, 'processId': processId, 'initiator': this.initiator }, $container).then((response) => {
            this.loadMessage(response, () => {
                this.loadAppointmentForm();
                this.loadQueueInfo();
                this.loadQueueTable();
                this.loadCalendar();
                hideSpinner();
            });
        });
    }

    onConfirm(event, template, callback) {
        stopEvent(event);
        this.selectedProcess = null;
        const processId = $(event.target).data('id');
        const name = $(event.target).data('name');
        this.loadCall(`${this.includeUrl}/dialog/?template=${template}&parameter[id]=${processId}&parameter[name]=${name}`).then((response) => {
            this.loadDialog(response, callback);
        });
    }

    onQueueProcess($container, event) {
        stopEvent(event);
        showSpinner($container);
        const sendData = $container.find('form').serializeArray();
        sendData.push({ name: 'queue', value: 1 });
        this.loadContent(`${this.includeUrl}/appointmentForm/`, 'POST', sendData, $container).then(() => {
            //this.loadAppointmentForm(true, true);
            this.loadQueueInfo();
            this.loadQueueTable();
            this.loadCalendar();
        });
    }

    onResetProcess($container, event) {
        let selectedProcess = $(event.target).data('id');
        this.loadContent(`${this.includeUrl}/process/queue/reset/?selectedprocess=${selectedProcess}&selecteddate=${this.selectedDate}`, 'GET', null, $container).then(() => {
            this.loadQueueInfo();
        });
    }

    onEditProcess(event) {
        this.selectedProcess = $(event.target).data('id');
        this.loadAppointmentForm();
    }

    onNextProcess() {
        //this.calledProcess = null;
        this.loadQueueInfo();
        this.loadQueueTable();
    }

    onCallNextProcess() {
        let exclude = $($.find('[data-called-process]')).data('calledProcess');
        const url = `${this.includeUrl}/workstation/process/cancel/next/?exclude=` + exclude
        return this.loadContent(url, 'GET', null, $('.client-next'));
    }

    onCancelNextProcess() {
        //console.log('CANCEL');
        this.calledProcess = null;
        this.loadClientNext();
    }

    onReloadQueueTable(event) {
        stopEvent(event);
        this.loadQueueTable();
    }

    onPrintWaitingNumber(event) {
        stopEvent(event);
        const processId = $(event.target).data('id');
        $(event.target).closest('.message').fadeOut().remove();
        window.open(`${this.includeUrl}/process/queue/?print=1&selectedprocess=${processId}`)
    }

    onSendCustomMail($container, event) {
        stopEvent(event);
        const processId = $(event.target).data('process');
        const sendStatus = $(event.target).data('status');
        this.loadCall(`${this.includeUrl}/mail/?selectedprocess=${processId}&status=${sendStatus}&dialog=1`).then((response) => {
            this.loadDialog(response, (() => {
                showSpinner($container);
                const sendData = $('.dialog form').serializeArray();
                sendData.push(
                    { 'name': 'submit', 'value': 'form' },
                    { 'name': 'dialog', 'value': 1 }
                );
                this.loadCall(`${this.includeUrl}/mail/`, 'POST', $.param(sendData), false, $container).then(
                    (response) => this.loadMessage(response, () => {
                        this.loadQueueTable();
                    })
                );
            }))
        });
    }

    onSendCustomNotification($container, event) {
        stopEvent(event);
        const processId = $(event.target).data('process');
        const sendStatus = $(event.target).data('status');
        this.loadCall(`${this.includeUrl}/notification/?selectedprocess=${processId}&status=${sendStatus}&dialog=1`).then((response) => {
            this.loadDialog(response, (() => {
                showSpinner($container);
                const sendData = $('.dialog form').serializeArray();
                sendData.push(
                    { 'name': 'submit', 'value': 'form' },
                    { 'name': 'dialog', 'value': 1 }
                );
                this.loadCall(`${this.includeUrl}/notification/`, 'POST', $.param(sendData)).then(
                    (response) => this.loadMessage(response, () => {
                        this.loadQueueTable();
                    })
                );
            }))
        });
    }

    onSendNotificationReminder($container, event) {
        stopEvent(event);
        showSpinner($container);
        const processId = $(event.target).data('process');
        const sendData = {
            'selectedprocess': processId,
            'status': 'queued',
            'submit': 'reminder'
        }
        this.loadCall(`${this.includeUrl}/notification/`, 'POST', $.param(sendData)).then(
            (response) => this.loadMessage(response, () => {
                this.loadQueueTable();
            })
        );
    }

    onGhostWorkstationChange($container, event) {
        let selectedDate = this.selectedDate
        let ghostWorkstationCount = "-1";
        if (event.target.value > -1)
            ghostWorkstationCount = event.target.value;
        this.loadContent(`${this.includeUrl}/counter/queueInfo/?ghostworkstationcount=${ghostWorkstationCount}&selecteddate=${selectedDate}`, null, null, $container).then(() => {
            this.loadAllPartials();
        });
    }

    loadAllPartials() {
        return Promise.all([
            this.loadCalendar(),
            this.loadQueueTable(),
            this.loadAppointmentForm(),
            this.loadClientNext()
        ]);
    }

    loadReloadPartials() {
        if (this.$main.find('.lightbox').length == 0) {
            this.loadQueueInfo();
            this.loadQueueTable(false);
        }
    }

    loadCalendar(showLoader = true) {
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

    loadClientNext(showLoader = true) {
        return new ClientNextView($.find('[data-client-next]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            calledProcess: this.calledProcess,
            onNextProcess: this.onNextProcess,
            onCallNextProcess: this.onCallNextProcess,
            onCancelNextProcess: this.onCancelNextProcess,
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
            slotsRequired: this.slotsRequired || 1,
            slotType: this.slotType,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            onDeleteProcess: this.onDeleteProcess,
            onEditProcess: this.onEditProcess,
            onQueueProcess: this.onQueueProcess,
            onSaveProcess: this.onSaveProcess,
            onCopyProcess: this.onCopyProcess,
            onChangeScope: this.onChangeScope,
            onAbortProcess: this.onAbortProcess,
            onCancelAppointmentForm: this.onCancelAppointmentForm,
            onPrintWaitingNumber: this.onPrintWaitingNumber,
            onAbortMessage: this.onAbortMessage,
            onSelectDateWithOverlay: this.onSelectDateWithOverlay,
            onChangeSlotCount: this.onChangeSlotCount,
            onConfirm: this.onConfirm,
            showLoader: showLoader,
            constructOnly: constructOnly
        });
    }

    loadQueueTable(showLoader = true) {
        return new QueueView($.find('[data-queue-table]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            onDeleteProcess: this.onDeleteProcess,
            onEditProcess: this.onEditProcess,
            onNextProcess: this.onNextProcess,
            onCallNextProcess: this.onCallNextProcess,
            onCancelNextProcess: this.onCancelNextProcess,
            onResetProcess: this.onResetProcess,
            onAbortMessage: this.onAbortMessage,
            onSendCustomMail: this.onSendCustomMail,
            onSendCustomNotification: this.onSendCustomNotification,
            onSendNotificationReminder: this.onSendNotificationReminder,
            onChangeTableView: this.onChangeTableView,
            onConfirm: this.onConfirm,
            onReloadQueueTable: this.onReloadQueueTable,
            showLoader: showLoader
        })
    }

    loadQueueInfo(showLoader = true) {
        return new QueueInfoView($.find('[data-queue-info]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            onGhostWorkstationChange: this.onGhostWorkstationChange,
            showLoader: showLoader
        })
    }

    loadAppointmentTimes(showLoader = true) {
        return new AppointmentTimesView($.find('[data-appointment-times]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            showLoader: showLoader
        })
    }

}

export default View;

/* global window */
import BaseView from '../../lib/baseview'
import { stopEvent, showSpinner, hideSpinner } from '../../lib/utils'
import $ from 'jquery'
import moment from 'moment'
import settings from '../../settings'
import AppointmentView from '../../block/appointment'
import AppointmentTimesView from '../../block/appointment/times'
import QueueView from '../../block/queue'
import QueueInfoView from '../../block/queue/info'
import CalendarView from '../../block/calendar'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element).focus();
        this.includeUrl = options.includeurl;
        this.selectedDate = options['selected-date'];
        this.selectedTime = options['selected-time'];
        this.selectedProcess = options['selected-process'];
        this.slotType = 'intern';
        this.slotsRequired = 0;
        this.reloadTimer;
        this.lastReload = 0;
        this.bindPublicMethods(
            'loadAllPartials',
            'onAbortMessage',
            'onAbortProcess',
            'onChangeScope',
            'onPrintWaitingNumber',
            'onDatePick',
            'onDateToday',
            'onSelectDateWithOverlay',
            'onNextProcess',
            'onDeleteProcess',
            'onConfirm',
            'onEditProcess',
            'onSaveProcess',
            'onQueueProcess',
            'onResetProcess',
            'onSendCustomMail',
            'onSendCustomNotification',
            'onSendNotificationReminder',
            'onReloadQueueTable',
            'onChangeTableView',
            'onGhostWorkstationChange'
        );
        this.$.ready(() => {
            this.loadData;
            this.setLastReload();
            this.setReloadTimer();
        });
        $.ajaxSetup({ cache: false });
        this.loadAllPartials().then(() => this.bindEvents());
        console.log('Component: Counter', this, options);
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
        this.$main.find('[data-queue-table], [data-queue-info]').mouseenter(() => {
            //console.log("stop Reload on mouse enter");
            clearTimeout(this.reloadTimer);
        });
        this.$main.find('[data-queue-table], [data-queue-info]').mouseleave(() => {
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
        if (this.selectedProcess) {
            this.loadCalendar();
            this.loadQueueTable();
            this.loadQueueInfo();
            this.loadAppointmentForm(true, true);
            this.loadAppointmentTimes();
            this.$main.find('.add-date-picker input#process_date').val(moment(this.selectedDate, 'YYYY-MM-DD').format('DD.MM.YYYY'));
            this.$main.find('input#process_selected_date').val(moment(this.selectedDate, 'YYYY-MM-DD').format('YYYY-MM-DD'));
            this.$main.find('.appointment-form .switchcluster select').val(this.selectedScope);
            this.$main.find('[name="familyName"]').focus();
          } else {
              this.loadAllPartials();
          }

    }

    onSelectDateWithOverlay() {
        const container = $.find('[data-calendar]');
        $(container).find('.calendar').addClass('lightbox__content');
        $(container).addClass('lightbox').on('click', () => {
            $(container).removeClass('lightbox');
            $(container).removeClass('lightbox__content');
        });
    }

    onChangeScope(scopeId) {
        this.selectedScope = scopeId;
        this.loadCalendar();
        this.loadAppointmentForm(true, true);
        //this.$main.find('.appointment-form .switchcluster select').val(this.selectedScope);
    }

    onChangeTableView ($container, event, $element) {
        const pathName = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')).split('/').pop();
        $element.val(pathName);
        $(event.target).closest('form').submit();
    }

    onDateToday($container, event) {
        this.onDatePick($container, event)
    }

    onSaveProcess ($container, event, action = 'update') {
        stopEvent(event);
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
        stopEvent(event);
        this.selectedProcess = null;
        this.loadAppointmentForm(true, false, $container);
    }

    onAbortMessage(event)
    {
        stopEvent(event);
        $(event.target).closest('.message').fadeOut().remove();
    }

    onDeleteProcess ($container, event) {
        const processId  = $(event.target).data('id');
        if ($container) {
            return this.loadContent(`${this.includeUrl}/appointmentForm/`, 'POST', {'delete': 1, 'processId': processId, 'initiator': this.initiator}, $container).then(() => {
                  this.loadAppointmentForm(true, true, $container);
                  this.loadQueueTable();
                  this.loadCalendar();
            });
        } else {
          showSpinner($container);
          return this.loadCall(`${this.includeUrl}/appointmentForm/`, 'POST', {'delete': 1, 'processId': processId, 'initiator': this.initiator}).then((response) => {
                this.loadMessage(response, () => {
                    this.loadQueueTable();
                    this.loadCalendar();
                    hideSpinner();
                });
          });
        }

    }

    onConfirm(event, template, callback)
    {
      stopEvent(event);
      this.selectedProcess = null;
      const processId  = $(event.target).data('id');
      const name  = $(event.target).data('name');
      this.loadCall(`${this.includeUrl}/dialog/?template=${template}&parameter[id]=${processId}&parameter[name]=${name}`).then((response) => {
          this.loadDialog(response, callback);
      });
    }

    onQueueProcess ($container, event) {
        stopEvent(event);
        showSpinner($container);
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

    onEditProcess (event) {
        this.selectedProcess = $(event.target).data('id');
        this.loadAppointmentForm();
    }

    onNextProcess() {
        this.calledProcess = null;
        this.loadQueueTable();
    }

    onReloadQueueTable(event) {
        stopEvent(event);
        this.loadQueueTable();
    }

    onPrintWaitingNumber (event) {
        stopEvent(event);
        const processId  = $(event.target).data('id');
        $(event.target).closest('.message').fadeOut().remove();
        window.open(`${this.includeUrl}/process/queue/?print=1&selectedprocess=${processId}`)
    }

    onSendCustomMail ($container, event) {
        stopEvent(event);
        const processId = $(event.target).data('process');
        const sendStatus = $(event.target).data('status');
        this.loadCall(`${this.includeUrl}/mail/?selectedprocess=${processId}&status=${sendStatus}&dialog=1`).then((response) => {
            this.loadDialog(response, (() => {
                showSpinner($container);
                const sendData = $('.dialog form').serializeArray();
                sendData.push(
                    {'name': 'submit', 'value':'form'},
                    {'name': 'dialog', 'value':1}
                );
                this.loadCall(`${this.includeUrl}/mail/`, 'POST', $.param(sendData)).then(
                    (response) => this.loadMessage(response, () => {
                        this.loadQueueTable();
                    })
                );
            }))
        });
    }

    onSendCustomNotification ($container, event) {
        stopEvent(event);
        const processId = $(event.target).data('process');
        const sendStatus = $(event.target).data('status');
        this.loadCall(`${this.includeUrl}/notification/?selectedprocess=${processId}&status=${sendStatus}&dialog=1`).then((response) => {
            this.loadDialog(response, (() => {
                showSpinner($container);
                const sendData = $('.dialog form').serializeArray();
                sendData.push(
                    {'name': 'submit', 'value':'form'},
                    {'name': 'dialog', 'value':1}
                );
                this.loadCall(`${this.includeUrl}/notification/`, 'POST', $.param(sendData)).then(
                    (response) => this.loadMessage(response, () => {
                        this.loadQueueTable();
                    })
                );
            }))
        });
    }

    onSendNotificationReminder (event) {
        stopEvent(event);
        showSpinner();
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

    onGhostWorkstationChange() {
        this.loadAllPartials();
    }

    loadAllPartials() {
        return Promise.all([
            this.loadCalendar(),
            this.loadAppointmentForm(),
            this.loadQueueTable(),
            this.loadQueueInfo(),
            this.loadAppointmentTimes()
        ])
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
            onSelectDateWithOverlay: this.onSelectDateWithOverlay,
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
            onSendCustomNotification: this.onSendCustomNotification,
            onSendNotificationReminder: this.onSendNotificationReminder,
            onChangeTableView: this.onChangeTableView,
            onConfirm: this.onConfirm,
            onReloadQueueTable: this.onReloadQueueTable,
            showLoader: showLoader
        })
    }

    loadAppointmentTimes (showLoader = true) {
        return new AppointmentTimesView(this.$main.find('[data-appointment-times]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            showLoader: showLoader
        })
    }

    loadQueueInfo (showLoader = true) {
        return new QueueInfoView(this.$main.find('[data-queue-info]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            onGhostWorkstationChange: this.onGhostWorkstationChange,
            showLoader: showLoader
        })
    }

}

export default View;

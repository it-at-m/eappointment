import BaseView from '../../lib/baseview'
import { stopEvent, showSpinner, hideSpinner } from '../../lib/utils'
import $ from 'jquery'
import settings from '../../settings'
import AppointmentView from '../../block/appointment'
import QueueView from '../../block/queue'
import CalendarView from '../../block/calendar'
import HeaderScopeView from '../../block/scope/header'
import ClientNextView from '../../block/process/next'
import QueueInfoView from '../../block/queue/info'
import AppointmentTimesView from '../../block/appointment/times'
import ValidationHandler from '../../lib/validationHandler'


class View extends BaseView {
    constructor(element, options) {
        super(element);
        this.page = 'workstation';
        this.element = $(element).focus();
        this.includeUrl = options.includeurl;
        this.selectedTime = options['selected-time'];
        this.selectedDate = options['selected-date'];
        this.selectedProcess = options['selected-process'];
        this.clusterEnabled = options['cluster-enabled'] || false;
        this.emailConfirmationActivated = options['email-confirmation-activated'] || 0;
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
            'onPrintProcessMail',
            'onDatePick',
            'onDateToday',
            'addFocusTrap',
            'onNextProcess',
            'onCallNextProcess',
            'onCancelNextProcess',
            'onDeleteProcess',
            'onConfirm',
            'onEditProcess',
            'onSaveProcess',
            'onChangeProcess',
            'onReserveProcess',
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
        $(() => {
            this.setLastReload();
            this.setReloadTimer();
        });
        $.ajaxSetup({
            cache: false
        });
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
        this.$main.find('[data-queue-table]').on("mouseenter", () => {
            //console.log("stop Reload on mouse enter");
            clearTimeout(this.reloadTimer);
        });
        this.$main.find('[data-queue-table]').on("mouseleave", () => {
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
            this.setLastReload();
        }, 1000);
    }

    onDatePick(date) {
        this.selectedDate = date;
        this.loadCalendar();
        this.loadClientNext(true, false);
        if ('counter' == this.page)
            this.loadQueueInfo();
        this.loadQueueTable();
        this.loadAppointmentForm(true, true);
    }

    addFocusTrap(elem) {
        // Get all focusable elements inside our trap container
        var tabbable = elem.find('select, input, textarea, button, a, *[role="button"]');
        // Focus the first element
        if (tabbable.length) {
            tabbable.filter(':visible').first().focus();
        }
        tabbable.bind('keydown', function (e) {
            if (e.keyCode === 9) { // TAB pressed
                // we need to update the visible last and first focusable elements everytime tab is pressed,
                // because elements can change their visibility
                var firstVisible = tabbable.filter(':visible').first();
                var lastVisible = tabbable.filter(':visible').last();
                if (firstVisible && lastVisible) {
                    if (e.shiftKey && ($(firstVisible)[0] === $(this)[0])) {
                        // TAB + SHIFT pressed on first visible element
                        e.preventDefault();
                        lastVisible.focus();
                    }
                    else if (!e.shiftKey && ($(lastVisible)[0] === $(this)[0])) {
                        // TAB pressed pressed on last visible element
                        e.preventDefault();
                        firstVisible.focus();
                    }
                }
            }
        });
    }

    onChangeSlotCount(event) {
        this.slotsRequired = $(event.currentTarget).val();
        this.loadCalendar();
    }

    onChangeScope(event, calendarOnly = false) {
        stopEvent(event);
        this.selectedScope = $(event.currentTarget).val();
        if (calendarOnly) {
            this.loadCalendar();
        } else {
            this.loadCalendar();
            this.loadAppointmentForm();
        }
    }

    onChangeTableView(event, changeScope = false) {
        stopEvent(event);
        if (changeScope) this.selectedScope = $(event.currentTarget).val();
        const sendData = $(event.currentTarget).closest('form').serializeArray();
        this.loadCall(`${this.includeUrl}/workstation/select/`, 'POST', sendData).then(() => {
            if (changeScope && this.selectedScope == 'cluster') {
                window.location.href = `${this.includeUrl}/workstation/`
            } else if (changeScope && this.selectedScope != 'cluster') {
                this.loadAllPartials(false);
            } else {
                return Promise.all([
                    this.loadQueueTable(),
                    this.loadQueueInfo()
                ]);
            }

        });
    }

    onDateToday(date) {
        this.onDatePick(date)
    }

    onQueueProcess(scope, event, isCopy = false) {
        stopEvent(event);
        showSpinner(scope.$main);
        const sendData = scope.$main.find('form').serializeArray();
        if (this.selectedProcess && !isCopy) {
            sendData.push({ name: 'selectedprocess', value: this.selectedProcess });
        }
        this.loadCall(`${this.includeUrl}/process/queue/`, 'POST', sendData, false, scope.$main).then((response) => {
            var validator = new ValidationHandler(scope, { response: response });
            if (validator.hasErrors()) {
                return validator.render();
            } else {
                this.selectedProcess = null;
                this.loadMessage(response, () => {
                    this.loadAppointmentForm();
                    if ('counter' == this.page)
                        this.loadQueueInfo();
                    this.loadQueueTable();
                    this.loadCalendar();
                }, null, event.currentTarget);
            }
        }).then(() => {
            hideSpinner();
        });

    }

    onReserveProcess(scope, event, isCopy = false) {
        stopEvent(event);
        showSpinner(scope.$main);
        const sendData = scope.$main.find('form').serializeArray();
        sendData.push({ name: 'initiator', value: this.initiator });
        if (this.selectedProcess && !isCopy) {
            sendData.push({ name: 'selectedprocess', value: this.selectedProcess });
        }
        this.loadCall(`${this.includeUrl}/process/reserve/`, 'POST', sendData, false, scope.$main).then((response) => {
            var validator = new ValidationHandler(scope, { response: response });
            if (validator.hasErrors()) {
                return validator.render();
            } else {
                this.loadMessage(response, () => {
                    this.selectedProcess = null;
                    this.loadAppointmentForm();
                }, scope.$main, event.currentTarget);
            }
        }).then(() => {
            if ('counter' == this.page)
                this.loadQueueInfo();
            this.loadQueueTable();
            this.loadCalendar();
            hideSpinner(scope.$main);
        });
    }

    onChangeProcess(scope, event) {
        stopEvent(event);
        showSpinner(scope.$main);
        const sendData = scope.$main.find('form').serializeArray();
        sendData.push({ name: 'initiator', value: this.initiator });
        if (this.selectedProcess) {
            sendData.push({ name: 'selectedprocess', value: this.selectedProcess });
        }
        this.loadCall(`${this.includeUrl}/process/change/`, 'POST', sendData, false, scope.$main).then((response) => {
            var validator = new ValidationHandler(scope, { response: response });
            if (validator.hasErrors()) {
                return validator.render();
            } else {
                this.loadMessage(response, () => {
                    this.selectedProcess = null;
                    this.loadAppointmentForm();
                    if ('counter' == this.page)
                        this.loadQueueInfo();
                    this.loadQueueTable();
                    this.loadCalendar();
                }, scope.$main, event.currentTarget);
            }
        }).then(() => {
            hideSpinner();
        });
    }

    onSaveProcess(scope, event) {
        stopEvent(event);
        showSpinner(scope.$main);
        if ($(event.currentTarget).data('id')) {
            this.selectedProcess = $(event.currentTarget).data('id');
        }
        const sendData = scope.$main.find('form').serializeArray();
        sendData.push({ name: 'initiator', value: this.initiator });
        this.loadCall(`${this.includeUrl}/process/${this.selectedProcess}/save/`, 'POST', sendData, false, scope.$main).then((response) => {
            var validator = new ValidationHandler(scope, { response: response });
            if (validator.hasErrors()) {
                return validator.render();
            } else {
                this.loadMessage(response, () => {
                    this.loadAppointmentForm();
                    if ('counter' == this.page)
                        this.loadQueueInfo();
                    this.loadQueueTable();
                    this.loadCalendar();
                }, null, event.currentTarget);
            }
        }).then(() => {
            hideSpinner();
        });
    }

    onCopyProcess(scope, event) {
        stopEvent(event);
        var selectedTime = $('select#process_time').val();
        if (0 == $('select#process_time').find(':selected').data('free')) {
            this.loadCall(`${this.includeUrl}/dialog/?template=copy_failed_time_unvalid&parameter[selectedtime]=${selectedTime}`).then((response) => {
                this.loadDialog(response, () => {
                    this.onAbortMessage(event)
                }, null, event.currentTarget);
            });
            return false;
        }
        var withAppointment = ('00-00' != selectedTime);
        if (withAppointment) {
            this.onReserveProcess(scope, event, true);
        } else {
            this.onQueueProcess(scope, event, true);
        }
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
        $(event.currentTarget).closest('.message').fadeOut().remove();
    }

    onDeleteProcess(event) {
        stopEvent(event);
        this.selectedProcess = null;
        const processId = $(event.currentTarget).data('id');
        showSpinner();
        this.loadCall(`${this.includeUrl}/process/${processId}/delete/?initiator=${this.initiator}`).then((response) => {
            this.loadMessage(response, () => {
                this.loadAppointmentForm();
                if ('counter' == this.page)
                    this.loadQueueInfo();
                this.loadQueueTable();
                this.loadCalendar();
                hideSpinner();
            }, null, event.currentTarget);
        });
    }

    onConfirm(event, template, callback, abortCallback) {
        stopEvent(event);
        this.selectedProcess = null;
        const processId = $(event.currentTarget).data('id');
        const name = $(event.currentTarget).data('name');
        var url = `${this.includeUrl}/dialog/?template=${template}`;
        if (processId || name) {
            url = url + `& parameter[id]=${processId}& parameter[name]=${name}`;
        }
        this.loadCall(url).then((response) => {
            this.loadDialog(response, callback, abortCallback, event.currentTarget);

            const dialog = document.getElementsByClassName('dialog')[0]
            dialog.focus();
        })
    }

    onResetProcess(event) {
        let selectedProcess = $(event.currentTarget).data('id');
        let url = `${this.includeUrl}/process/queue/reset/?selectedprocess=${selectedProcess}&selecteddate=${this.selectedDate}`
        this.loadCall(url).then((response) => this.loadMessage(response, () => {
                this.loadQueueTable();
                if ('counter' == this.page)
                    this.loadQueueInfo();
            }, null, event.currentTarget)
        );
    }

    onEditProcess(event) {
        this.selectedProcess = $(event.currentTarget).data('id');
        this.selectedScope = $(event.currentTarget).data('scope-id');
        this.loadAppointmentForm();
        this.loadCalendar();
    }

    onNextProcess() {
        //this.calledProcess = null;
        if ('counter' == this.page)
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
        this.loadClientNext(true, false);
    }

    onReloadQueueTable(event) {
        stopEvent(event);
        this.loadQueueTable();
    }

    onPrintWaitingNumber(event) {
        stopEvent(event);
        this.selectedProcess = $(event.currentTarget).data('id');
        $(event.currentTarget).closest('.message').fadeOut().remove();
        window.open(`${this.includeUrl}/process/queue/?print=1&selectedprocess=${this.selectedProcess}`)
        this.selectedProcess = null;
        this.loadAppointmentForm();
    }

    onPrintProcessMail(event) {
        stopEvent(event);
        this.selectedProcess = $(event.currentTarget).data('id');
    
        // URL for mail_confirmation.twig
        const url = `${this.includeUrl}/process/queue/?print=1&printType=mail&selectedprocess=${this.selectedProcess}`;
        
        // Ajax request to get content from mail_confirmation.twig
        $.ajax({
            url: url,
            success(data) {
                // Creating new window
                const printWindow = window.open('', '', 'height=800,width=1000');
                printWindow.document.write(data);
                printWindow.document.write(`
                    <script>
                        window.onload = function() {
                            window.print();
                            window.close();
                        }
                    <\/script>`
                );
                printWindow.document.close();
            },
            error() {
                alert('Der Inhalt konnte nicht geladen werden.');
            }
        });
    }

    onSendCustomMail($container, event) {
        stopEvent(event);
        const processId = $(event.currentTarget).data('process');
        this.loadCall(`${this.includeUrl}/mail/?selectedprocess=${processId}&dialog=1`).then((response) => {
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
                    }, null, event.currentTarget)
                );
            }), null, event.currentTarget)
        });
    }

    onSendCustomNotification($container, event) {
        stopEvent(event);
        const processId = $(event.currentTarget).data('process');
        this.loadCall(`${this.includeUrl}/notification/?selectedprocess=${processId}&dialog=1`).then((response) => {
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
                    }, null, event.currentTarget)
                );
            }), null, event.currentTarget)
        });
    }

    onSendNotificationReminder($container, event) {
        stopEvent(event);
        showSpinner($container);
        const processId = $(event.currentTarget).data('process');
        const sendData = {
            'selectedprocess': processId,
            'submit': 'reminder'
        }
        this.loadCall(`${this.includeUrl}/notification/`, 'POST', $.param(sendData)).then(
            (response) => this.loadMessage(response, () => {
                this.loadQueueTable();
            }, null, event.currentTarget)
        );
    }

    onGhostWorkstationChange($container, event) {
        let selectedDate = this.selectedDate
        let ghostWorkstationCount = "-1";
        if (event.currentTarget.value > -1)
            ghostWorkstationCount = event.currentTarget.value;
        this.loadContent(`${this.includeUrl}/counter/queueInfo/?ghostworkstationcount=${ghostWorkstationCount}&selecteddate=${selectedDate}`, null, null, $container).then(() => {
            this.loadAllPartials(false);
        });
    }

    loadAllPartials(callProcess = true) {
        return Promise.all([
            this.loadClientNext(true, callProcess),
            this.loadAppointmentForm(),
            this.loadCalendar(),
            this.loadQueueTable(),
            this.loadHeaderScope()
        ]);
    }

    loadReloadPartials() {
        if (this.$main.find('.lightbox').length == 0) {
            if ('counter' == this.page)
                this.loadQueueInfo(false);
            this.loadQueueTable(false);
        }
    }

    loadHeaderScope() {
        return new HeaderScopeView($.find('[data-header-scope]'), {
            includeUrl: this.includeUrl
        })
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

    loadClientNext(showLoader = true, loadProcess = true) {
        return new ClientNextView($.find('[data-client-next]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            calledProcess: loadProcess ? this.calledProcess : null,
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
            clusterEnabled: this.clusterEnabled,
            emailConfirmationActivated: this.emailConfirmationActivated,
            includeUrl: this.includeUrl,
            slotsRequired: this.slotsRequired || 1,
            slotType: this.slotType,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            onDeleteProcess: this.onDeleteProcess,
            onEditProcess: this.onEditProcess,
            onQueueProcess: this.onQueueProcess,
            onSaveProcess: this.onSaveProcess,
            onChangeProcess: this.onChangeProcess,
            onReserveProcess: this.onReserveProcess,
            onCopyProcess: this.onCopyProcess,
            onChangeScope: this.onChangeScope,
            onAbortProcess: this.onAbortProcess,
            onCancelAppointmentForm: this.onCancelAppointmentForm,
            onPrintWaitingNumber: this.onPrintWaitingNumber,
            onPrintProcessMail: this.onPrintProcessMail,
            onAbortMessage: this.onAbortMessage,
            onChangeSlotCount: this.onChangeSlotCount,
            onConfirm: this.onConfirm,
            showLoader: showLoader,
            constructOnly: constructOnly
        });
    }

    loadQueueTable(showLoader = true) {
        return new QueueView($.find('[data-queue-table]'), {
            selectedDate: this.selectedDate,
            selectedScope: this.selectedScope,
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
            onChangeScope: this.onChangeScope,
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

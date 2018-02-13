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
        this.bindPublicMethods('loadAllPartials', 'onAbortProcess', 'onChangeScope', 'onDatePick', 'onDateToday', 'onNextProcess','onDeleteProcess','onEditProcess','onSaveProcess','onQueueProcess');
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

    onSaveProcess (processId, event) {
        this.selectedProcess = null;
        if (processId)
            this.selectedProcess = processId;
        this.loadAppointmentForm();
        this.loadQueueTable();
        this.loadCalendar();
    }

    onAbortProcess(event) {
        this.ActionHandler.abort(event);
        this.selectedProcess = null;
        this.$main.data('selected-process', '');
        this.loadAppointmentForm();
    }

    onDeleteProcess () {
        this.selectedProcess = null;
        this.loadAppointmentForm();
        this.loadQueueTable();
        this.loadCalendar();
    }

    onQueueProcess () {
        this.selectedProcess = null;
        this.loadAppointmentForm();
        this.loadQueueTable();
    }

    onEditProcess (processId) {
        this.selectedProcess = processId;
        this.loadAppointmentForm();
    }

    onNextProcess() {
        this.calledProcess = null;
        this.loadQueueTable();
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
        return new CalendarView(this.$main.find('[data-calendar]'), {
            selectedDate: this.selectedDate,
            selectedScope: this.selectedScope,
            selectedProcess: this.selectedProcess,
            slotsRequired: this.slotsRequired,
            slotType: this.slotType,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            includeUrl: this.includeUrl,
            showLoader: showLoader
        })
    }

    loadClientNext (showLoader = true) {
        return new ClientNextView(this.$main.find('[data-client-next]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            calledProcess: this.calledProcess,
            onNextProcess: this.onNextProcess,
            showLoader: showLoader
        })
    }

    loadAppointmentForm(showLoader = true, constructOnly = false) {
        return new AppointmentView(this.$main.find('[data-appointment-form]'), {
            source: 'workstation',
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
            showLoader: showLoader,
            constructOnly: constructOnly
        })
    }

    loadQueueTable (showLoader = true) {
        return new QueueView(this.$main.find('[data-queue-table]'), {
            source: 'workstation',
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            onDeleteProcess: this.onDeleteProcess,
            onEditProcess: this.onEditProcess,
            onNextProcess: this.onNextProcess,
            showLoader: showLoader
        })
    }

}

export default View;

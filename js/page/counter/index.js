/* global window */
import BaseView from '../../lib/baseview'
import $ from 'jquery'
import settings from '../../settings'
import AppointmentView from '../../block/appointment'
import AppointmentTimesView from '../../block/appointment/times'
import QueueView from '../../block/queue'
import QueueInfoView from '../../block/queue/info'
import CalendarView from '../../block/calendar'
import ActionHandler from "../../block/appointment/action"
import FreeProcessList from "../../block/appointment/free-process-list"
import FormButtons from '../../block/appointment/form-buttons'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element).focus();
        this.includeUrl = options.includeurl;
        this.selectedDate = options['selected-date'];
        this.selectedTime = options['selected-time'];
        this.selectedProcess = options['selected-process'];
        this.ActionHandler = (new ActionHandler(this.$main.find('[data-appointment-form]'), options));
        this.slotType = 'intern';
        this.slotsRequired = 0;
        this.reloadTimer;
        this.lastReload = 0;
        this.bindPublicMethods('loadAllPartials', 'onDatePick', 'onNextProcess', 'onDateToday', 'onGhostWorkstationChange','onDeleteProcess','onEditProcess','onSaveProcess','onQueueProcess');
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

    onDatePick(date, full = false) {
        this.selectedProcess = null;
        this.selectedDate = date;
        if (full) {
            this.loadAllPartials();
        } else {
            this.loadCalendar(),
            this.loadQueueTable(),
            this.loadQueueInfo(),
            this.loadAppointmentTimes()
            this.ActionHandler.setSelectedDate(date);
            (new FormButtons(this.$main.find('[data-form-buttons]'), {
                "includeUrl": this.includeUrl,
                "selectedDate": this.selectedDate,
                "selectedProcess": this.selectedProcess
            })).load();
            (new FreeProcessList(this.$main.find('[data-free-process-list]'), {
                "includeUrl": this.includeUrl,
                "slotType": this.slotType,
                "selectedDate": this.selectedDate,
                "selectedTime": this.selectedTime,
                "slotsRequired": this.slotsRequired
            })).loadList();
            this.$main.find('[name="familyName"]').focus();
        }
    }

    onDateToday(date, full = false) {
        this.selectedProcess = null;
        this.selectedDate = date;
        if (full) {
            this.loadAllPartials();
        } else {
            this.loadCalendar(),
            this.loadQueueTable(),
            this.loadQueueInfo(),
            this.loadAppointmentTimes()
            this.ActionHandler.setSelectedDate(date);
            (new FormButtons(this.$main.find('[data-form-buttons]'), {
                "includeUrl": this.includeUrl,
                "selectedDate": this.selectedDate,
                "selectedProcess": this.selectedProcess
            })).load();
            (new FreeProcessList(this.$main.find('[data-free-process-list]'), {
                "includeUrl": this.includeUrl,
                "slotType": this.slotType,
                "selectedDate": this.selectedDate,
                "selectedTime": this.selectedTime,
                "slotsRequired": this.slotsRequired
            })).loadList();
            this.$main.find('[name="familyName"]').focus();
        }
    }

    onNextProcess() {
        this.loadQueueTable();
    }

    onDeleteProcess () {
        this.selectedProcess = null;
        this.loadAppointmentForm();
        this.loadQueueTable();
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

    onSaveProcess (processId) {
        if (processId)
            this.selectedProcess = processId;
        this.loadAppointmentForm();
        this.loadQueueTable();
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

    loadCalendar (showLoader = true) {
        return new CalendarView(this.$main.find('[data-calendar]'), {
            selectedDate: this.selectedDate,
            slotsRequired: this.slotsRequired,
            slotType: this.slotType,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            includeUrl: this.includeUrl,
            showLoader: showLoader
        })
    }

    loadReloadPartials() {
        if (this.$main.find('.lightbox').length == 0) {
            this.loadQueueTable(false);
            this.loadQueueInfo(false);
        }
    }

    loadAppointmentForm(showLoader = true) {
        return new AppointmentView(this.$main.find('[data-appointment-form]'), {
            source: 'counter',
            selectedDate: this.selectedDate,
            selectedTime: this.selectedTime,
            selectedProcess: this.selectedProcess,
            includeUrl: this.includeUrl,
            slotsRequired: this.slotsRequired,
            slotType: this.slotType,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            onDeleteProcess: this.onDeleteProcess,
            onQueueProcess: this.onQueueProcess,
            onSaveProcess: this.onSaveProcess,
            showLoader: showLoader
        })
    }

    loadQueueTable (showLoader = true) {
        return new QueueView(this.$main.find('[data-queue-table]'), {
            source: 'counter',
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

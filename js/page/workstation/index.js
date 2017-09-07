import BaseView from '../../lib/baseview'
import $ from 'jquery'
import AppointmentView from '../../block/appointment'
import QueueView from '../../block/queue'
import CalendarView from '../../block/calendar'
import ClientNextView from '../../block/process/next'
import ActionHandler from "../../block/appointment/action"
import FreeProcessList from "../../block/appointment/free-process-list"

const reloadInterval = 60; //seconds

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element).focus();
        this.includeUrl = options.includeurl;
        this.selectedDate = options['selected-date'];
        this.selectedTime = options['selected-time'];
        this.selectedProcess = options['selected-process'];
        this.calledProcess = options['called-process'];
        this.ActionHandler = (new ActionHandler(this.$main.find('[data-appointment-form]'), options));
        this.slotType = 'intern';
        this.slotsRequired = 0;
        this.reloadTimer;
        this.lastReload = 0;
        this.bindPublicMethods('loadAllPartials', 'onDatePick', 'onDateToday', 'onNextProcess','onDeleteProcess','onEditProcess','onSaveProcess','onQueueProcess');
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
            if (this.lastReload > reloadInterval) {
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
        }, reloadInterval * 1000);
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
            this.loadCalendar()
            this.loadClientNext()
            this.loadQueueTable();
            this.ActionHandler.setSelectedDate(date);
            (new FreeProcessList(this.$main.find('[data-free-process-list]'), {
                "includeUrl": this.includeUrl,
                "slotType": this.slotType,
                "selectedDate": this.selectedDate,
                "selectedTime": this.selectedTime,
                "slotsRequired": this.slotsRequired
            })).loadList();
        }
    }

    onDateToday(date, full = false) {
        this.selectedProcess = null;
        this.selectedDate = date;
        if (full) {
            this.loadAllPartials();
        } else {
            this.loadCalendar()
            this.loadClientNext()
            this.loadQueueTable();
            this.ActionHandler.setSelectedDate(date);
            (new FreeProcessList(this.$main.find('[data-free-process-list]'), {
                "includeUrl": this.includeUrl,
                "slotType": this.slotType,
                "selectedDate": this.selectedDate,
                "selectedTime": this.selectedTime,
                "slotsRequired": this.slotsRequired
            })).loadList();
        }
    }

    onDeleteProcess () {
        this.selectedProcess = null;
        this.loadAppointmentForm();
        this.loadQueueTable();
    };

    onQueueProcess () {
        this.selectedProcess = null;
        this.loadAppointmentForm();
        this.loadQueueTable();
    };

    onEditProcess (processId) {
        this.selectedProcess = processId;
        this.loadAppointmentForm();
    };

    onSaveProcess (processId) {
        if (processId)
            this.selectedProcess = processId;
        this.loadAppointmentForm();
        this.loadQueueTable();
    }

    onNextProcess() {
        this.calledProcess = null;
        this.loadQueueTable();
    }

    loadAllPartials() {
        this.selectedProcess = null;
        return Promise.all([
            this.loadCalendar(),
            this.loadClientNext(),
            this.loadAppointmentForm(),
            this.loadQueueTable()
        ])
    }

    loadReloadPartials() {
        this.loadQueueTable();
    }

    loadCalendar () {
        return new CalendarView(this.$main.find('[data-calendar]'), {
            selectedDate: this.selectedDate,
            slotsRequired: this.slotsRequired,
            slotType: this.slotType,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            includeUrl: this.includeUrl
        })
    }

    loadClientNext () {
        return new ClientNextView(this.$main.find('[data-client-next]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            calledProcess: this.calledProcess,
            onNextProcess: this.onNextProcess
        })
    }

    loadAppointmentForm() {
        return new AppointmentView(this.$main.find('[data-appointment-form]'), {
            source: 'workstation',
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
            onSaveProcess: this.onSaveProcess
        })
    }

    loadQueueTable () {
        return new QueueView(this.$main.find('[data-queue-table]'), {
            source: 'workstation',
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            onDeleteProcess: this.onDeleteProcess,
            onEditProcess: this.onEditProcess,
            onNextProcess: this.onNextProcess
        })
    }

}

export default View;

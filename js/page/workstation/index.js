import BaseView from '../../lib/baseview'
import $ from 'jquery'
import AppointmentView from '../../block/appointment'
import QueueView from '../../block/queue'
import CalendarView from '../../block/calendar'
import ClientNextView from '../../block/process/next'


class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.selectedDate = options['selected-date'];
        this.selectedTime = options['selected-time'];
        this.selectedProcess = options['selected-process'];
        this.bindPublicMethods('loadAllPartials', 'onDatePick', 'onDateToday', 'onNextProcess','onDeleteProcess','onEditProcess','onSaveProcess','onQueueProcess');
        this.$.ready(this.loadData);
        $.ajaxSetup({ cache: false });
        this.loadAllPartials().then(() => this.bindEvents());
        console.log('Component: Workstation', this, options);
    }

    bindEvents() {
    }

    onDatePick(date) {
        this.selectedProcess = null;
        this.selectedDate = date;
        this.loadAllPartials();
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

    onDateToday(date) {
        this.selectedProcess = null;
        this.selectedDate = date;
        this.loadCalendar();
        this.loadAppointmentForm();
        this.loadQueueTable();
    }

    onNextProcess() {
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

    loadCalendar () {
        return new CalendarView(this.$main.find('[data-calendar]'), {
            selectedDate: this.selectedDate,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            includeUrl: this.includeUrl
        })
    }

    loadClientNext () {
        return new ClientNextView(this.$main.find('[data-client-next]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
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
            onEditProcess: this.onEditProcess
        })
    }

}

export default View;

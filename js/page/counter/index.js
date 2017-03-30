import BaseView from '../../lib/baseview'
import $ from 'jquery'
import AppointmentView from '../../block/appointment'
import AppointmentTimesView from '../../block/appointment/times'
import QueueView from '../../block/queue'
import QueueInfoView from '../../block/queue/info'
import CalendarView from '../../block/calendar'

import { loadInto } from './utils'
import { lightbox } from '../../lib/utils'


class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.selectedDate = options['selected-date'];
        this.selectedProcess = options['selected-process'];
        this.bindPublicMethods('loadAllPartials', 'selectDateWithOverlay', 'onDatePick', 'onDateToday', 'onGhostWorkstationChange');
        this.$.ready(this.loadData);
        $.ajaxSetup({ cache: false });
        this.loadAllPartials().then(() => this.bindEvents());
        console.log('Component: Counter', this, options);
    }

    bindEvents() {

    }

    selectDateWithOverlay() {
        return new Promise((resolve, reject) => {
            const destroyCalendar = () => {
                tempCalendar.destroy()
            }

            const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {
                destroyCalendar()
                reject()
            })

            const tempCalendar = new CalendarView(lightboxContentElement, {
                includeUrl: this.includeUrl,
                selectedDate: this.selectedDate,
                onDatePick: (date) => {
                    destroyCalendar()
                    destroyLightbox()
                    //resolve(date);
                },
                onDateToday: (date) => {
                    destroyCalendar()
                    destroyLightbox()
                    //resolve(date);
                }
            })
        });
    }

    onDatePick(date) {
        this.selectedDate = date;
        this.loadAllPartials();
    }

    onDateToday(date) {
        this.selectedDate = date;
        this.loadCalendar(),
        this.loadAppointmentForm(),
        this.loadQueueTable(),
        this.loadAppointmentTimes()
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

    loadCalendar () {
        return new CalendarView(this.$main.find('[data-calendar]'), {
            selectedDate: this.selectedDate,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
            includeUrl: this.includeUrl
        })
    }

    loadAppointmentForm() {
        return new AppointmentView(this.$main.find('[data-appointment-form]'), {
            selectedDate: this.selectedDate,
            selectedProcess: this.selectedProcess,
            includeUrl: this.includeUrl,
            selectDateWithOverlay: this.selectDateWithOverlay,
        })
    }

    loadAppointmentTimes () {
        return new AppointmentTimesView(this.$main.find('[data-appointment-times]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl
        })
    }

    loadQueueInfo () {
        return new QueueInfoView(this.$main.find('[data-queue-info]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            onGhostWorkstationChange: this.onGhostWorkstationChange
        })
    }

    loadQueueTable () {
        return new QueueView(this.$main.find('[data-queue-table]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            onDatePick: this.onDatePick,
            onDateToday: this.onDateToday,
        })
    }

}

export default View;

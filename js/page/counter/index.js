import BaseView from '../../lib/baseview'
import $ from 'jquery'
import AppointmentView from '../../block/appointment'
import QueueView from '../../block/queue'
import CalendarView from './calendar'

import { loadInto } from './utils'


class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.selectedDate = options['selected-date'];
        this.bindPublicMethods('loadAllPartials', 'selectDateWithOverlay', 'onDatePick');
        this.$.ready(this.loadData);
        $.ajaxSetup({ cache: false });
        this.loadAllPartials().then(() => this.bindEvents());
        console.log('Component: Counter', this, options);
    }

    bindEvents() {
        this.element.off('click').on('click', '.queue-table .reload', (ev) => {
            ev.preventDefault();
            this.loadQueueTable();
        }).on('change', '.queue-table .switchcluster select', (ev) => {
            $(ev.target).closest('form').submit();
        }).on('change', '.queue-table .appointmentsOnly input', (ev) => {
            $(ev.target).closest('form').submit();
        })

        this.element.on('click', '.appointment-form input[name=date]', (ev) => {
            console.log('date click')
            this.selectDateWithOverlay()
                   .then(date => ev.target.value = date)
                   .catch(() => console.log('no date selected'));
        })
    }

    selectDateWithOverlay() {
        return new Promise((resolve, reject) => {
            const overlay = this.$main.find('[data-calendar-overlay]');
            overlay.off('click');
            this.$main.attr('data-show-calendar-overlay', true);

            const close = () => {
                this.$main.removeAttr('data-show-calendar-overlay');
                tempCalendar.destroy()
            }

            const tempCalendar = new CalendarView(overlay, {
                includeUrl: this.includeUrl,
                selectedDate: this.selectedDate,
                onDatePick: (date) => {
                    close()
                    resolve(date);
                }
            })

            overlay.on('click', () => {
                close()
                reject()
            })
        });
    }

    onDatePick(date) {
        this.selectedDate = date;
        this.loadAllExceptCalendar();
    }

    loadAllPartials() {
        return Promise.all([
            this.loadCalendar(),
            this.loadAllExceptCalendar()
        ])
    }

    loadAllExceptCalendar() {
        return Promise.all([
            this.loadAppointmentForm(),
            this.loadQueueTable(),
            this.loadQueueInfo()
        ]);
    }

    loadCalendar () {
        return new CalendarView(this.$main.find('[data-calendar]'), {
            selectedDate: this.selectedDate,
            onDatePick: this.onDatePick,
            includeUrl: this.includeUrl
        })
    }

    loadAppointmentForm() {
        const url = `${this.includeUrl}/counter/appointmentForm/?selecteddate=${this.selectedDate}`
        this.loadAppointmentFormPromise = loadInto(url, this.element.find('[data-appointment-form]'), AppointmentView)
        return this.loadAppointmentFormPromise;
    }

    loadQueueInfo () {
        const url = `${this.includeUrl}/counter/queueInfo/?selecteddate=${this.selectedDate}`
        this.loadQueueInfoPromise = loadInto(url, this.element.find('[data-queue-info]'))
        return this.loadQueueInfoPromise;
    }

    loadQueueTable () {
        const url = `${this.includeUrl}/counter/queueTable/?selecteddate=${this.selectedDate}`
        this.loadQueueTablePromise = loadInto(url, this.element.find('[data-queue-table]'), QueueView)
        return this.loadQueueTablePromise;
    }

}

export default View;

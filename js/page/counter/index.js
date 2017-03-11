import BaseView from '../../lib/baseview'
import $ from 'jquery'
import AppointmentView from '../../block/appointment'
import QueueView from '../../block/queue'

const loadInto = (url, container, view) => {
    container.find('.body').html(loaderHtml);

    return new Promise((resolve, reject) => {
        $.ajax(url, {
            method: 'GET'
        }).done(data => {
            container.empty();
            container.html(data);
            if (view) {
                new view(container);
            }
            resolve(container);
        }).fail(err => {
            console.log('XHR error', url, err)
            reject(err);
        })
    })
}

const loaderHtml = '<div class="loader"></div>'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.selectedDate = options['selected-date'];
        this.bindPublicMethods('loadAllPartials', 'selectDateWithOverlay');
        this.$.ready(this.loadData);
        $.ajaxSetup({ cache: false });
        this.loadAllPartials().then(() => this.bindEvents());
        console.log('Component: Counter', this, options);
    }

    bindEvents() {
        this.element.off('click').on('click', '[data-calendar].calendar-page .body a', (ev) => {
            ev.preventDefault();
            this.selectedDate = $(ev.target).attr('data-date');
            this.element.attr('data-selected-date', this.selectedDate);
            this.loadAllExceptCalendar();
        }).on('click', '[data-calendar] .calendar-navigation .pagelink', (ev) => {
            ev.preventDefault();
            this.selectedDate = $(ev.target).attr('data-date');
            this.element.attr('data-selected-date', this.selectedDate);
            this.loadCalendar();
        }).on('click', '.calendar-navigation .pagedaylink', (ev) => {
            ev.preventDefault();
            this.selectedDate = $(ev.target).attr('data-date');
            this.element.attr('data-selected-date', this.selectedDate);
            this.loadAllPartials();
        }).on('click', '.queue-table .reload', (ev) => {
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
        return this.loadCalendarPromise.then((calendarElement) => {
            return new Promise((resolve, reject) => {
                const calendar = calendarElement.clone();
                const overlay = this.element.find('[data-calendar-overlay]');
                console.log(calendar);
                overlay.empty()
                overlay.append(calendar);
                this.element.attr('data-show-calendar-overlay', true);
                overlay.off('click').on('click', '.calendar-page .body a', (ev) => {
                    ev.stopPropagation();
                    this.element.removeAttr('data-show-calendar-overlay');
                    const date = $(ev.target).attr('data-date');
                    resolve(date);
                }).on('click', () => {
                    this.element.removeAttr('data-show-calendar-overlay');
                    reject();
                })
            })
        })
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
        const url = `${this.includeUrl}/counter/calendar/?source=counter&selecteddate=${this.selectedDate}`
        this.loadCalendarPromise = loadInto(url, this.element.find('[data-calendar]'))
        return this.loadCalendarPromise;
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

import BaseView from '../../lib/baseview'
import $ from 'jquery'
import ApppointmentView from '../../block/appointment'

const loadInto = (url, container, view) => {
    const body = document.createElement('div');
    const old = container.find('.body')
    $(body).addClass('body').html(loaderHtml);
    $(body).insertBefore(old);
    old.remove() // make sure all binded events are removed

    return new Promise((resolve, reject) => {
        $.ajax(url, {
            method: 'GET'
        }).done(data => {
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
        this.bindPublicMethods('loadAllPartials');
        console.log('Component: Counter', this, options);
        $.ajaxSetup({ cache: false });
        this.loadAllPartials();
        this.bindEvents();
    }

    bindEvents() {
        this.element.off('click').on('click', '.calendar-page .body a', (ev) => {
            ev.preventDefault();
            this.selectedDate = $(ev.target).attr('data-date');
            this.element.attr('data-selected-date', this.selectedDate);
            this.loadAllExceptCalendar();
        }).on('click', '.calendar-navigation .pagemonthlink', (ev) => {
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
    }

    loadAllPartials() {
        this.loadCalendar();
        this.loadAllExceptCalendar();
    }

    loadAllExceptCalendar() {
        this.loadAppointmentForm();
        this.loadQueueTable();
        this.loadQueueInfo();
    }

    loadCalendar () {
        const url = `${this.includeUrl}/counter/calendar/?source=counter&selecteddate=${this.selectedDate}`
        loadInto(url, this.element.find('[data-calendar]'))
    }

    loadAppointmentForm() {
        const url = `${this.includeUrl}/counter/appointmentForm/?selecteddate=${this.selectedDate}`
        loadInto(url, this.element.find('[data-appointment-form]'), ApppointmentView)
    }

    loadQueueInfo () {
        const url = `${this.includeUrl}/counter/queueInfo/?selecteddate=${this.selectedDate}`
        loadInto(url, this.element.find('[data-queue-info]'))
    }

    loadQueueTable () {
        const url = `${this.includeUrl}/counter/queueTable/?selecteddate=${this.selectedDate}`
        loadInto(url, this.element.find('[data-queue-table]'))
    }

}

export default View;

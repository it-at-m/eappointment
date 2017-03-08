import BaseView from '../../lib/baseview'
import $ from 'jquery'

const loadInto = (url, container) => {
    container.find('.body').html(loaderHtml);

    return new Promise((resolve, reject) => {
        $.ajax(url, {
            method: 'GET'
        }).done(data => {
            container.html(data);
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
        this.$.ready(this.loadData);
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
        }).on('click', '.calendar-navigation .pagelink', (ev) => {
            ev.preventDefault();
            this.selectedDate = $(ev.target).attr('data-date');
            this.element.attr('data-selected-date', this.selectedDate);
            this.loadCalendar();
        }).on('change', '.queue-table .switchcluster select', (ev) => {
            console.log('scope cluster switch changed');
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
        loadInto(url, this.element.find('[data-appointment-form]'))
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

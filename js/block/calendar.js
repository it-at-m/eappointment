import BaseView from '../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.selectedDate = options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.onDatePick = options.onDatePick || (() => {});
        this.onDateToday = options.onDateToday || (() => {});
        this.slotsRequired = 0;
        this.slotType = 'intern';
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Calendar', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/calendarPage/?selecteddate=${this.selectedDate}&slottype=${this.slotType}&slotsrequired=${this.slotsRequired}`
        this.loadPromise = this.loadContent(url)
        return this.loadPromise;
    }

    bindEvents() {
        this.$main.on('click', '.calendar-page .body a', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const selectedDate = $(ev.target).attr('data-date');
            console.log('date selected', selectedDate)
            this.onDatePick(selectedDate, this)
        }).on('click', '.calendar-navigation .pagemonthlink', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.selectedDate = $(ev.target).attr('data-date');
            console.log('month selected', this.selectedDate)
            this.load();
        }).on('click', '.calendar-navigation .today', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const selectedDate = $(ev.target).attr('data-date');
            console.log('today selected', selectedDate)
            this.onDateToday(selectedDate, this)
        })
    }
}

export default View

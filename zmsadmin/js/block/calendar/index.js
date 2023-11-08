import BaseView from '../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.selectedDate = options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.showLoader = options.showLoader || false;
        this.onDatePick = options.onDatePick || (() => {});
        this.onDateToday = options.onDateToday || (() => {});
        this.slotsRequired = options.slotsRequired;
        this.selectedProcess = options.selectedProcess || 0;
        this.selectedScope = options.selectedScope || 0;
        this.slotType = options.slotType;
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        //console.log('Component: Calendar', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/calendarPage/?selecteddate=${this.selectedDate}&slotType=${this.slotType}&slotsRequired=${this.slotsRequired}&selectedscope=${this.selectedScope}&selectedprocess=${this.selectedProcess}`
        this.loadPromise = this.loadContent(url, 'GET', null, null, this.showLoader)
        return this.loadPromise;
    }

    bindEvents() {
        this.$main.on('click', '.calendar-page table a', (ev) => {
            this.onDatePick($(ev.currentTarget).attr('data-date'));
        }).on('click', '.calendar-navigation a.pagemonthlink', (ev) => {
            this.onDatePick($(ev.currentTarget).attr('data-date'));
        }).on('click', '.calendar-navigation .today', (ev) => {
            this.onDateToday($(ev.currentTarget).attr('data-date'));
        })
    }
}

export default View

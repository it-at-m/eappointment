import BaseView from '../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {
    constructor (element, options) {
        super(element, options);
        this.selectedDate = this.options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.onDatePick = options.onDatePick || (() => {});
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Calendar', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/counter/calendar/?source=counter&selecteddate=${this.selectedDate}`
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
            console.log('pagemonthlink', this.selectedDate)
            this.load();
        })
    }
}

export default View


import BaseView from "../../lib/baseview"
import $ from "jquery"
import moment from 'moment'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.$main = $(element);
        this.source = options.source;
        this.selectedDate = options.selectedDate;
        this.selectedTime = options.selectedTime;
        this.includeUrl = options.includeUrl || "";
        this.selectedProcess = options.selectedProcess;
        $.ajaxSetup({ cache: false });
        console.log('Component: Process actions', this, options);
    }

    addnew (ev) {
        console.log("New Button clicked", ev);
        this.loadNew();
    }

    finishList(ev) {
        console.log("Finish List Pickup Button clicked", ev);
        ev.preventDefault();
        ev.stopPropagation();
        var idList = this.$main.find(".process-finish").map((index, item) => {
            return $(item).data('id');
        }).get();
        this.showSpinner();
        var deleteFromQueue = () => {
            let processId = idList.shift();
            if (processId) {
                let url = `${this.includeUrl}/pickup/delete/${processId}/`;
                if (idList.length == 0) {
                    url = url + "?list=1";
                    return this.loadCall(url, 'DELETE');
                }
                return this.loadCall(url, 'DELETE').then(deleteFromQueue);
            }
        }
        return deleteFromQueue();
    }

    finish (ev) {
        console.log("Finish Pickup Button clicked", ev);
        ev.preventDefault();
        ev.stopPropagation();
        const id  = $(ev.target).data('id');
        const url = `${this.includeUrl}/pickup/delete/${id}/`;
        return this.loadCall(url, 'DELETE');
    }

    stopEvent (ev) {
        if (ev) {
            ev.preventDefault();
            ev.stopPropagation();
        }
    }

    pickup (ev) {
        console.log("Pickup Button clicked", ev);
        ev.preventDefault();
        ev.stopPropagation();
        const processId  = $(ev.target).data('id')
        const url = `${this.includeUrl}/pickup/call/${processId}/`
        return this.loadCall(url);
    }

    pickupDirect (processId) {
        const url = `${this.includeUrl}/pickup/call/${processId}/`
        return this.loadCall(url);
    }

    sendNotificationReminder (ev) {
        ev.preventDefault();
        ev.stopPropagation();
        const id = $(ev.target).data('id');
        const url = `${this.includeUrl}/notification/`;
        return this.loadCall(url, 'POST', {
            'selectedprocess': id,
            'status': 'queued',
            'submit': 'reminder'
        });
    }

    sendNotification (ev, url) {
        ev.preventDefault();
        ev.stopPropagation();
        const selectedProcessId = $(ev.target).data('process');
        const sendStatus = $(ev.target).data('status');
        return this.loadCall(url + `?selectedprocess=${selectedProcessId}&status=${sendStatus}&dialog=1`);
    }

    sendMail (ev, url) {
        ev.preventDefault();
        ev.stopPropagation();
        const selectedProcessId = $(ev.target).data('process');
        const sendStatus = $(ev.target).data('status');
        return this.loadCall(url + `?selectedprocess=${selectedProcessId}&status=${sendStatus}&dialog=1`);
    }

    reset (ev) {
        console.log("Reset to Queue Button clicked", ev);
        ev.preventDefault();
        ev.stopPropagation();
        let selectedProcess = $(ev.target).data('id');
        const url = `${this.includeUrl}/process/queue/reset/?selectedprocess=${selectedProcess}`;
        return this.loadCall(url);
    }

    cancel (ev) {
        console.log("Cancel Button clicked");
        if (ev) {
            ev.preventDefault();
            ev.stopPropagation();
        }
        const url = `${this.includeUrl}/workstation/process/cancel/?noredirect=1`;
        return this.loadCall(url);
    }

    setSelectedDate (date) {
        this.$main.find('.add-date-picker input#process_date').val(moment(date, 'YYYY-MM-DD').format('DD.MM.YYYY'));
        this.$main.find('input#process_selected_date').val(moment(date, 'YYYY-MM-DD').format('YYYY-MM-DD'));
        this.removeCalendarOverlay();
    }

    selectDateWithOverlay () {
        let calendarBox = $.find('[data-calendar]');
        $('.calendar').addClass('lightbox__content');
        $(calendarBox).addClass('lightbox').on('click', (ev) => {
            console.log('Overlay calendar background click', ev);
            ev.stopPropagation()
            ev.preventDefault()
            this.removeCalendarOverlay()
        }).on('click', '.lightbox__content', (ev) => {
            ev.stopPropagation();
        });
    }

    removeCalendarOverlay () {
        let calendarBox = $.find('[data-calendar]');
        $(calendarBox).removeClass('lightbox');
        $('.calendar').removeClass('lightbox__content');
    }
}

export default View;

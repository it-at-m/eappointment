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

    queue (ev) {
        console.log("Queue Button clicked", ev);
        this.selectedDate = moment(this.$main.find('form #process_date').val(), 'DD.MM.YYYY').format('YYYY-MM-DD');
        const sendData = this.$main.find('form').serialize();
        const url = `${this.includeUrl}/process/${this.selectedDate}/queue/`;
        return this.loadCall(url, 'POST', sendData);
    }

    printWaitingNumber () {
        let selectedDate = moment(this.$main.find('form #process_date').val(), 'DD.MM.YYYY').format('YYYY-MM-DD');
        let selectedProcess = this.$main.find('[data-id]').data('id');
        window.open(`${this.includeUrl}/process/${selectedDate}/queue/?print=1&selectedprocess=${selectedProcess}`)
    }

    delete (ev) {
        console.log("Delete Button clicked", ev);
        let initiator = (this.source == "counter") ? "Tresen" : "Arbeitsplatz";
        ev.preventDefault();
        ev.stopPropagation();
        const id  = $(ev.target).data('id')
        const name  = $(ev.target).data('name')
        const ok = confirm('Wenn Sie den Kunden Nr. '+ id +' '+ name +' löschen wollen, klicken Sie auf OK. Der Kunde wird darüber per eMail und/oder SMS informiert.)')
        const url = `${this.includeUrl}/process/${id}/delete/?initiator=${initiator}`;
        if (ok) {
            return this.loadCall(url, 'DELETE');
        }
        return Promise.resolve(false);
    }

    finishList(ev) {
        console.log("Finish List Pickup Button clicked", ev);
        ev.preventDefault();
        ev.stopPropagation();
        const ok = confirm('Wollen Sie wirklich alle Abholer aus dieser Liste löschen?)')
        if (ok) {
            var idList = this.$main.find(".process-finish").map((index, item) => {
                return $(item).data('id');
            }).get().join();
            const url = `${this.includeUrl}/pickup/delete/${idList}/`;
            return this.loadCall(url, 'DELETE');
        }
        return Promise.resolve(false);
    }

    finish (ev) {
        console.log("Finish Pickup Button clicked", ev);
        ev.preventDefault();
        ev.stopPropagation();
        const id  = $(ev.target).data('id');
        const url = `${this.includeUrl}/pickup/delete/${id}/`;
        const name  = $(ev.target).data('name');
        const withoutconfirmation  = $(ev.target).data('no-confirmation');
        if (withoutconfirmation) {
            return this.loadCall(url, 'DELETE');
        }
        const ok = confirm('Wenn Sie den Kunden Nr. '+ id +' '+ name +' löschen und ins Archiv verschieben wollen, klicken Sie auf OK.')
        if (ok) {
            return this.loadCall(url, 'DELETE');
        }
        return Promise.resolve(false);
    }

    reserve (ev) {
        console.log("Reserve Button clicked", ev);
        this.selectedDate = moment(this.$main.find('form #process_date').val(), 'DD.MM.YYYY').format('YYYY-MM-DD');
        this.selectedTime = this.$main.find('form #process_time').val();
        const sendData = this.$main.find('form').serialize();
        const url = `${this.includeUrl}/process/${this.selectedDate}/${this.selectedTime}/reserve/`;
        return this.loadCall(url, 'POST', sendData);
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

    save (ev) {
        console.log("Save Button clicked", ev);
        let initiator = (this.source == "counter") ? "Tresen" : "Arbeitsplatz";
        const sendData = this.$main.find('form').serialize();
        const url = `${this.includeUrl}/process/${this.selectedProcess}/save/?initiator=${initiator}`;
        return this.loadCall(url, 'POST', sendData);
    }

    sendNotificationReminder (ev) {
        ev.preventDefault();
        ev.stopPropagation();
        const selectedProcessId = $(ev.target).data('process');
        const url = `${this.includeUrl}/notification/`;
        const ok = confirm('Möchten Sie dem Kunden per SMS mitteilen, dass er/sie bald an der Reihe ist, dann klicken Sie auf OK.')
        if (ok) {
            return this.loadCall(url, 'POST', {
                'selectedprocess': selectedProcessId,
                'status': 'queued',
                'submit': 'reminder'
            });
        }
        return Promise.resolve(false);
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

    abort (ev) {
        console.log("Abort Button clicked");
        this.selectedProcess = null;
        if (ev) {
            ev.preventDefault();
            ev.stopPropagation();
        }
    }

    setSelectedDate (date) {
        this.$main.find('.add-date-picker input#process_date').val(moment(date, 'YYYY-MM-DD').format('DD.MM.YYYY'));
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

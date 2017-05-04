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
        ev.preventDefault();
        ev.stopPropagation();
        const id  = $(ev.target).data('id')
        const name  = $(ev.target).data('name')
        const ok = confirm('Wenn Sie den Kunden Nr. '+ id +' '+ name +' löschen wollen, klicken Sie auf OK. Der Kunde wird darüber per eMail und/oder SMS informiert.)')
        const url = `${this.includeUrl}/process/${id}/delete/?initiator=backend-${this.source}`;
        if (ok) {
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

    save (ev) {
        console.log("Save Button clicked", ev);
        const sendData = this.$main.find('form').serialize();
        const url = `${this.includeUrl}/process/${this.selectedProcess}/save/?initiator=backend-${this.source}`;
        return this.loadCall(url, 'POST', sendData);
    }

    sendNotificationReminder (ev) {
        ev.preventDefault();
        ev.stopPropagation();
        const selectedProcessId = $(ev.target).data('process');
        const url = `${this.includeUrl}/notification/send/`;
        const ok = confirm('Möchten Sie dem Kunden per SMS mitteilen, dass er/sie bald an der Reihe ist, dann klicken Sie auf OK.')
        if (ok) {
            return this.loadCall(url, 'POST', { 'selectedProcess': selectedProcessId, 'status': 'queued' });
        }
        return Promise.resolve(false);
    }

    sendNotification (ev, url) {
        ev.preventDefault();
        ev.stopPropagation();
        const selectedProcessId = $(ev.target).data('process');
        return this.loadCall(url + `?selectedprocess=${selectedProcessId}&dialog=1`);
    }

    sendMail (ev, url) {
        ev.preventDefault();
        ev.stopPropagation();
        const selectedProcessId = $(ev.target).data('process');
        return this.loadCall(url + `?selectedprocess=${selectedProcessId}&dialog=1`);
    }

    reset (ev) {
        console.log("Reset to Queue Button clicked", ev);
        ev.preventDefault();
        ev.stopPropagation();
        let selectedProcess = $(ev.target).data('id');
        const url = `${this.includeUrl}/process/queue/reset/?selectedprocess=${selectedProcess}`;
        return this.loadCall(url);
    }
}

export default View;

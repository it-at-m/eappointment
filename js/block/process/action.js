import BaseView from "../../lib/baseview"
import $ from "jquery"
import moment from 'moment'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.$main = $(element);
        this.selectedDate = options.selectedDate;
        this.selectedTime = options.selectedTime;
        this.includeUrl = options.includeUrl || "";
        this.selectedProcess = options.selectedProcess;
        $.ajaxSetup({ cache: false });
        console.log('Component: Process actions', this, options);
    }

    addnew (ev) {
        console.log("New Button pressed", ev);
        this.loadNew();
    }

    queue (ev) {
        console.log("Queue Button pressed", ev);
        return false;
    }

    delete (ev) {
        console.log("Delete Button pressed", ev);
        const id  = $(ev.target).data('id')
        const authkey  = $(ev.target).data('authkey')
        const name  = $(ev.target).data('name')
        const ok = confirm('Wenn Sie den Kunden Nr. '+ id +' '+ name +' löschen wollen, klicken Sie auf OK. Der Kunde wird darüber per eMail und/oder SMS informiert.)')
        const url = `${this.includeUrl}/process/${id}/delete/`;
        if (ok) {
            return this.loadCall(url, 'DELETE');
        }
        return this.loadPromise;
    }

    reserve (ev) {
        console.log("Reserve Button pressed", ev);
        this.selectedDate = moment(this.$main.find('form #process_date').val(), 'DD.MM.YYYY').format('YYYY-MM-DD');
        this.selectedTime = this.$main.find('form #process_time').val();
        const sendData = this.$main.find('form').serialize();
        const url = `${this.includeUrl}/process/${this.selectedDate}/${this.selectedTime}/reserve/`;
        return this.loadCall(url, 'POST', sendData);
    }

    save (ev) {
        console.log("Save Button pressed", ev);
        const sendData = this.$main.find('form').serialize();
        const url = `${this.includeUrl}/process/${this.selectedProcess}/save/`;
        return this.loadCall(url, 'POST', sendData);
    }
}

export default View;

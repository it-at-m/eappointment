import BaseView from "../../lib/baseview"
import $ from "jquery"

const loaderHtml = '<div class="loader-small"></div>'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.selectedDate = options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.onDatePick = options.onDatePick || (() => {});
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Queue', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/queueTable/?selecteddate=${this.selectedDate}`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadErrorCallback(source, url) {
        if (source == 'button') {
            return this.loadContent(url)
        } else if (source == 'lightbox') {
            console.log('lightbox closed without action call');
        } else {
            const defaultUrl = `${this.includeUrl}/workstation/process/cancel/`
            return this.loadContent(defaultUrl)
        }
    }

    bindEvents() {
        this.$main.off('click').on('click', '.queue-table .reload', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.load();
        }).on('change', '.queue-table .switchcluster select', (ev) => {
            $(ev.target).closest('form').submit();
        }).on('change', '.queue-table .appointmentsOnly input', (ev) => {
            $(ev.target).closest('form').submit();
        }).on('click', '.queue-table a.process-edit', (ev) => {
            this.editProcess(ev);
        }).on('click', '.queue-table a.process-delete', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.deleteProcess(ev);
        }).on('click', '.queue-table .calendar-navigation a', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const selectedDate = $(ev.target).attr('data-date');
            this.onDatePick(selectedDate, this);
        })
    }

    editProcess (ev) {
        console.log("Edit Button pressed", ev);
        this.$.hide();
        return false;
    }

    deleteProcess (ev) {
        const id  = $(ev.target).data('id')
        const authkey  = $(ev.target).data('authkey')
        const name  = $(ev.target).data('name')
        const ok = confirm('Wenn Sie den Kunden Nr. '+ id +' '+ name +' löschen wollen, klicken Sie auf OK. Der Kunde wird darüber per eMail und/oder SMS informiert.)')
        const url = `${this.includeUrl}/process/${id}/${authkey}/delete/`;
        if (ok) {
            $(ev.target).hide();
            $(ev.target).closest('td').append(loaderHtml);
            $.ajax(url, {
                method: 'GET'
            }).done(() => {
                $(ev.target).closest('tr').fadeOut('slow', function(){
                    $(ev.target).remove();
                });
            }).fail(err => {
                console.log('ajax error', err);
            })
        }
    }
}

export default View;

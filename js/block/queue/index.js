/* global window */
import BaseView from "../../lib/baseview"
import $ from "jquery"
import ActionHandler from "../appointment/action"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.ActionHandler = new ActionHandler(element, options);
        this.source = options.source;
        this.selectedDate = options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.showLoader = options.showLoader || false;
        this.onDatePick = options.onDatePick || (() => {});
        this.onDateToday = options.onDateToday || (() => {});
        this.onDeleteProcess = options.onDeleteProcess || (() => {});
        this.onEditProcess = options.onEditProcess || (() => {});
        this.onNextProcess = options.onNextProcess || (() => {});
        this.onResetProcess = options.onResetProcess || (() => {});
        this.onSendCustomMail = options.onSendCustomMail || (() => {});
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Queue', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/queueTable/?selecteddate=${this.selectedDate}`
        return this.loadContent(url, 'GET', null, null, this.showLoader).catch(err => this.loadErrorCallback(err));
    }

    cleanReload () {
        this.load().then(() => {
            this.bindEvents();
        });
    }

    bindEvents() {
        this.$main.off('click').on('click', '.queue-table .reload', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.load();
        }).on('change', '.queue-table .switchcluster select', (ev) => {
            let loc = window.location;
            let pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/')).split('/').pop();
            $('.sourceSwitchCluster').val(pathName);
            $(ev.target).closest('form').submit();
        }).on('change', '.queue-table .appointmentsOnly input', (ev) => {
            let loc = window.location;
            let pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/')).split('/').pop();
            $('.sourceAppointmentsOnly').val(pathName);
            $(ev.target).closest('form').submit();
        }).on('click', 'a.process-edit', (ev) => {
            this.onEditProcess($(ev.target).data('id'))
        }).on('click', 'a.process-reset', (ev) => {
            this.onResetProcess(this.$main, ev);
        }).on('click', 'a.process-delete', (ev) => {
            this.onDeleteProcess(this.$main, ev, 'confirm');
        }).on('click', '.queue-table .calendar-navigation .pagedaylink', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const selectedDate = $(ev.target).attr('data-date');
            console.log('next or prev day selected', selectedDate)
            this.onDatePick(selectedDate, this);
        }).on('click', '.queue-table .calendar-navigation .today', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const selectedDate = $(ev.target).attr('data-date');
            console.log('today selected', selectedDate)
            this.onDateToday(selectedDate, this)
        }).on('click', '.queue-table .process-notification-send', (ev) => {
            const id  = $(ev.target).data('process')
            var confirmNotificationReminder = this.loadCall(`${this.includeUrl}/dialog/?template=confirm_notification_reminder&parameter[id]=${id}`);
            confirmNotificationReminder.catch(err => this.loadErrorCallback(err)).then((response) => {
                this.showSpinner();
                this.loadMessage(response, this.load);
            });
        }).on('click', '.process-custom-mail-send', (ev) => {
            this.onSendCustomMail(this.$main, ev);
        }).on('click', '.process-custom-notification-send', (ev) => {
            const url = `${this.includeUrl}/notification/`;
            this.ActionHandler.sendNotification(ev, url).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadDialog(response, this.load);
            });
        })
    }
}

export default View;

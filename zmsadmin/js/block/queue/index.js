import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor(element, options) {
        super(element, options);
        this.setOptions(options);
        this.setCallbacks(options);
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        this.load(this.withCalled);
    }

    setOptions(options) {
        this.selectedScope = options.selectedScope;
        this.source = options.source;
        this.selectedDate = options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.showLoader = options.showLoader || false;
    }

    setCallbacks(options) {
        this.onDatePick = options.onDatePick;
        this.onDateToday = options.onDateToday;
        this.onDeleteProcess = options.onDeleteProcess;
        this.onEditProcess = options.onEditProcess;
        this.onNextProcess = options.onNextProcess;
        this.onResetProcess = options.onResetProcess;
        this.onSendCustomMail = options.onSendCustomMail;
        this.onSendCustomNotification = options.onSendCustomNotification;
        this.onSendNotificationReminder = options.onSendNotificationReminder;
        this.onChangeTableView = options.onChangeTableView;
        this.onChangeScope = options.onChangeScope;
        this.onConfirm = options.onConfirm;
        this.onReloadQueueTable = options.onReloadQueueTable;
    }


    load(withCalled = false) {
        if (withCalled === false) {
            const storedState = localStorage.getItem('calledPanelOpen');
            withCalled = (storedState === 'true');
        }

        const url = `${this.includeUrl}/queueTable/?selecteddate=${this.selectedDate}&withCalled=${withCalled ? 1 : 0}`;

        return this.loadContent(url, 'GET', null, null, this.showLoader)
            .then(() => {
                const storedState = localStorage.getItem('calledPanelOpen');
                if (storedState === 'true') {
                    $('#called-appointments').addClass('active');
                    $('#called-appointments').next('.accordion-panel').css('display', 'block');
                    this.withCalled = true;
                } else {
                    $('#called-appointments').removeClass('active');
                    $('#called-appointments').next('.accordion-panel').css('display', 'none');
                    this.withCalled = false;
                }
            })
            .catch(err => this.loadErrorCallback(err));
    }


    bindEvents() {
        this.$main.off('click').on('click', '.queue-table .reload', (ev) => {
            this.onReloadQueueTable(ev);
        }).on('focus', '.queue-table .switchcluster select', (ev) => {
            this.selectedScope = ev.target.value;
        }).on('change', '.queue-table .switchcluster select', (ev) => {
            this.onConfirm(ev, "confirm_switch_scope",
                () => {
                    this.onChangeTableView(ev, true);
                },
                () => {
                    this.$main.find('.queue-table .switchcluster select').val(this.selectedScope);
                }
            )
        }).on('change', '.queue-table .appointmentsOnly input', (ev) => {
            this.onChangeTableView(ev);
        }).on('click', 'a.process-edit', (ev) => {
            this.onEditProcess(ev)
        }).on('click', 'a.process-reset', (ev) => {
            this.onResetProcess(ev);
        }).on('click', '#called-appointments', (ev) => {
            this.withCalled = ! this.withCalled
            localStorage.setItem('calledPanelOpen', this.withCalled ? 'true' : 'false');
            if (this.withCalled) {
                this.load(true)
            }
        }).on('click', 'a.process-delete', (ev) => {
            this.onConfirm(ev, "confirm_delete", () => { this.onDeleteProcess(ev) });
        }).on('click', '.queue-table .calendar-navigation .pagedaylink', (ev) => {
            this.onDatePick($(ev.currentTarget).attr('data-date'));
        }).on('click', '.queue-table .calendar-navigation .today', (ev) => {
            this.onDateToday($(ev.currentTarget).attr('data-date'))
        }).on('click', '.queue-table .process-notification-send', (ev) => {
            this.onConfirm(ev, "confirm_notification_reminder", () => { this.onSendNotificationReminder(this.$main, ev) });
        }).on('click', '.process-custom-mail-send', (ev) => {
            this.onSendCustomMail(this.$main, ev);
        }).on('click', '.process-custom-notification-send', (ev) => {
            this.onSendCustomNotification(this.$main, ev);
        })
    }
}

export default View;

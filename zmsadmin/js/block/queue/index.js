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
        this.includeWaitingClientsEffective = options.includeWaitingClientsEffective || false;
    }

    setCallbacks(options) {
        this.onDatePick = options.onDatePick;
        this.onDateToday = options.onDateToday;
        this.onDeleteProcess = options.onDeleteProcess;
        this.onEditProcess = options.onEditProcess;
        this.onNextProcess = options.onNextProcess;
        this.onResetProcess = options.onResetProcess;
        this.onSendCustomMail = options.onSendCustomMail;
        this.onChangeTableView = options.onChangeTableView;
        this.onChangeScope = options.onChangeScope;
        this.onConfirm = options.onConfirm;
        this.onReloadQueueTable = options.onReloadQueueTable;
    }


    load(withCalled = false) {
        const url = `${this.includeUrl}/queueTable/?selecteddate=${this.selectedDate}&withCalled=${withCalled ? 1 : 0}&includeWaitingClientsEffective=${this.includeWaitingClientsEffective ? 1 : 0}`;

        return this.loadContent(url, 'GET', null, null, this.showLoader)
            .then(() => {
                if (withCalled) {
                    $('#called-appointments').addClass('active');
                    $('#called-appointments').next('.accordion-panel').css('display', 'block');
                    this.withCalled = true;
                } else {
                    $('#called-appointments').removeClass('active');
                    $('#called-appointments').next('.accordion-panel').css('display', 'none');
                    this.withCalled = false;
                }
                this.updateWaitingClientsEffective();
            })
            .catch(err => this.loadErrorCallback(err));
    }

    updateWaitingClientsEffective() {
        const $workstationView = this.$main.closest('.workstation-view');
        const $source = this.$main.find('[data-queue-waiting-clients-effective]');
        const $target = $workstationView.find('[data-waiting-clients-effective]');
        const $row = $workstationView.find('[data-waiting-clients-row]');

        if ($source.length === 0 || $target.length === 0 || $row.length === 0) {
            return;
        }

        const waitingClients = Number($source.attr('data-count') || 0);
        const trafficLightClass = this.getWaitingClientsTrafficLightClass(waitingClients, $row);

        if (trafficLightClass === '') {
            return;
        }

        $target.text(waitingClients);

        $row
            .removeClass('green yellow orange red')
            .addClass(trafficLightClass);
    }

    getWaitingClientsTrafficLightClass(waitingClients, $row) {
        const greenMax = Number($row.attr('data-waiting-clients-green-max'));
        const yellowMax = Number($row.attr('data-waiting-clients-yellow-max'));
        const orangeMax = Number($row.attr('data-waiting-clients-orange-max'));

        if ([greenMax, yellowMax, orangeMax].some(Number.isNaN)) {
            return '';
        }

        if (waitingClients >= 0 && waitingClients <= greenMax) {
            return 'green';
        }

        if (waitingClients <= yellowMax) {
            return 'yellow';
        }

        if (waitingClients <= orangeMax) {
            return 'orange';
        }

        return 'red';
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
            if (this.withCalled) {
                this.load(true)
            }
        }).on('click', 'a.process-delete', (ev) => {
            this.onConfirm(ev, "confirm_delete", () => { this.onDeleteProcess(ev) });
        }).on('click', '.queue-table .calendar-navigation .pagedaylink', (ev) => {
            this.onDatePick($(ev.currentTarget).attr('data-date'));
        }).on('click', '.queue-table .calendar-navigation .today', (ev) => {
            this.onDateToday($(ev.currentTarget).attr('data-date'))
        }).on('click', '.process-custom-mail-send', (ev) => {
            this.onSendCustomMail(this.$main, ev);
        })
    }
}

export default View;

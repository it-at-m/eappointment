import $ from 'jquery';
import BaseView from '../../lib/baseview';
import CapacityTable from './capacityTable';
import CapacityChart from './capacityChart';
import ReportRefresh from './reportRefresh';
import AutoRefresh from './autoRefresh';

class View extends BaseView {
    constructor(element, options) {
        super(element, options);
        this.chart = null;
        this.chartValueMode = 'slots';
        this.chartChannelMode = 'total';
        this.chartHideEmptySlots = true;
        this.autoRefreshIntervalMs = 0;
        this.autoRefreshTimer = null;
        this.refreshInFlight = false;

        this.tableController = new CapacityTable(this);
        this.chartController = new CapacityChart(this);
        this.reportRefresh = new ReportRefresh(this);
        this.autoRefreshController = new AutoRefresh(this);

        this.bindEvents();
        this.chartController.initSparseTimelineFromDom();
        this.tableController.initSettingsFromDom();
        this.tableController.initDataFromDom();
        this.chartController.initFromDom();
        this.chartController.initChannelFromDom();
        this.chartController.syncChannelSelect();
        this.tableController.syncHeaders();
        this.tableController.render();
        this.autoRefreshController.initFromDom();
    }

    bindEvents() {
        this.$main.on('click', '.report-board--refresh', (ev) => {
            ev.preventDefault();
            this.reportRefresh.refresh();
        });
        this.$main.on('change', '.report-board--auto-refresh-interval', (ev) => {
            ev.preventDefault();
            this.autoRefreshController.setInterval(Number(ev.target.value));
        });
        $(window).on('beforeunload.capacityReport', () => {
            this.autoRefreshController.clearTimer();
        });
        this.$main.on('change', '.report-board--capacity-channel-select', (ev) => {
            ev.preventDefault();
            this.chartController.setChannelMode(ev.target.value);
        });
        this.$main.on('click', '.report-board--chart-minutes', (ev) => {
            ev.preventDefault();
            this.chartController.toggleValueMode();
        });
        this.$main.on('click', '.report-board--chart-download', (ev) => {
            ev.preventDefault();
            this.chartController.downloadPng();
        });
        this.$main.on('click', '.report-board--chart-sparse', (ev) => {
            ev.preventDefault();
            this.chartController.toggleSparseTimeline();
        });
    }
}

export default View;

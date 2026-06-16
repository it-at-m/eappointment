import BaseView from "../../lib/baseview"
import Chart from "chart.js/auto"
import { parseJsonText } from "../../lib/utils"

class View extends BaseView {
    static AUTO_REFRESH_STORAGE_KEY = 'zmsstatistic.capacityAutoRefreshSeconds';

    static CAPACITY_CHANNEL_STORAGE_KEY = 'zmsstatistic.capacityChannelMode';

    static AUTO_REFRESH_INTERVALS_SECONDS = [0, 5, 10, 30, 60];

    static CAPACITY_CHANNEL_MODES = ['total', 'public', 'intern_only'];

    constructor (element, options) {
        super(element, options);
        this.chart = null;
        this.chartValueMode = 'slots';
        this.chartChannelMode = 'total';
        this.chartHideEmptySlots = true;
        this.autoRefreshIntervalMs = 0;
        this.autoRefreshTimer = null;
        this.refreshInFlight = false;
        this.bindEvents();
        this.initSparseTimelineFromDom();
        this.initCapacityTableSettings();
        this.initCapacityTableDataFromDom();
        this.initChartFromDom();
        this.initCapacityChannelFromDom();
        this.syncCapacityChannelSelect();
        this.syncCapacityTableHeaders();
        this.renderCapacityTable();
        this.initAutoRefreshFromDom();
    }

    bindEvents() {
        this.$main.on('click', '.report-board--refresh', (ev) => {
            ev.preventDefault();
            this.refreshReportContent();
        });
        this.$main.on('change', '.report-board--auto-refresh-interval', (ev) => {
            ev.preventDefault();
            this.setAutoRefreshInterval(Number(ev.target.value));
        });
        $(window).on('beforeunload.warehouseReport', () => {
            this.clearAutoRefreshTimer();
        });
        this.$main.on('change', '.report-board--capacity-channel-select', (ev) => {
            ev.preventDefault();
            this.setCapacityChannelMode(ev.target.value);
        });
        this.$main.on('click', '.report-board--chart-minutes', (ev) => {
            ev.preventDefault();
            this.toggleChartValueMode();
        });
        this.$main.on('click', '.report-board--chart-download', (ev) => {
            ev.preventDefault();
            this.downloadChartPng();
        });
        this.$main.on('click', '.report-board--chart-sparse', (ev) => {
            ev.preventDefault();
            this.toggleChartSparseTimeline();
        });
    }

    initSparseTimelineFromDom() {
        const $button = this.$main.find('.report-board--chart-sparse');
        if (!$button.length) {
            return;
        }
        this.chartHideEmptySlots = $button.attr('aria-pressed') !== 'false';
    }

    readJsonPayload($root, scriptSelector, fallbackDomAttributeName, parseContextLabel) {
        const $script = $root.find(scriptSelector).first();
        if ($script.length) {
            const jsonPayload = parseJsonText($script.text(), parseContextLabel);
            if (jsonPayload !== null) {
                $script.remove();
                return jsonPayload;
            }
        }

        if (!fallbackDomAttributeName) {
            return null;
        }

        const $legacyPayloadHost = $root.find(`[${fallbackDomAttributeName}]`).first();
        if (!$legacyPayloadHost.length) {
            return null;
        }

        const jsonPayload = parseJsonText(
            $legacyPayloadHost.attr(fallbackDomAttributeName),
            parseContextLabel
        );
        if (jsonPayload !== null) {
            $legacyPayloadHost.attr(fallbackDomAttributeName, '');
        }

        return jsonPayload;
    }

    initChartFromDom() {
        const $chartist = this.$main.find('.chartist').first();
        if (!$chartist.length) {
            return;
        }

        const sparseData = this.readJsonPayload(
            this.$main,
            'script.report-board--chart-data-sparse',
            'data-chartist-sparse',
            'chart-sparse'
        );
        const fullData = this.readJsonPayload(
            this.$main,
            'script.report-board--chart-data-full',
            'data-chartist-full',
            'chart-full'
        );
        const chartData = this.readJsonPayload(
            this.$main,
            'script.report-board--chart-data',
            'data-chartist',
            'chart'
        );

        if (!sparseData && !fullData && !chartData) {
            return;
        }

        $chartist.text('[Initializing chart...]');
        this.chartPeriod = $chartist.attr('data-chart-period') || '';
        this.chartDateFrom = $chartist.attr('data-chart-date-from') || '';
        this.chartDateTo = $chartist.attr('data-chart-date-to') || '';

        if (sparseData && fullData) {
            this.chartDataSparse = sparseData;
            this.chartDataFull = fullData;
        } else if (chartData) {
            this.chartDataSparse = null;
            this.chartDataFull = chartData;
        } else {
            $chartist.text('Diagrammdaten konnten nicht geladen werden.');
            console.error('Warehouse report: incomplete chart payload', {
                sparseData: Boolean(sparseData),
                fullData: Boolean(fullData),
                chartData: Boolean(chartData),
            });
            return;
        }

        this.applyChartDataSelection();
        this.syncChartModeButton();
        this.syncSparseTimelineButton();
        this.syncChartDownloadButton();
        this.renderChartjs();
    }

    initCapacityTableSettings() {
        const $table = this.$main.find('.report-board--capacity-table').first();
        if (!$table.length) {
            return;
        }

        this.tableIsHourly = $table.attr('data-table-is-hourly') === '1';
        this.tableLabelSummary = $table.attr('data-label-summary') || 'Gesamt';
        this.tableLabelEmpty = $table.attr('data-label-empty') || 'Es wurden keine Daten gefunden';
    }

    initCapacityTableDataFromDom() {
        const $table = this.$main.find('.report-board--capacity-table').first();
        if (!$table.length) {
            return;
        }

        const sparseData = this.readJsonPayload(
            this.$main,
            'script.report-board--table-data-sparse',
            'data-table-sparse',
            'table-sparse'
        );
        const fullData = this.readJsonPayload(
            this.$main,
            'script.report-board--table-data-full',
            'data-table-full',
            'table-full'
        );

        if (!sparseData || !fullData) {
            return;
        }

        this.tableDataSparse = sparseData;
        this.tableDataFull = fullData;
    }

    getCapacityTableRowValue(row, variable, position) {
        if (row == null) {
            return undefined;
        }

        if (typeof row === 'object' && !Array.isArray(row) && row[variable] !== undefined) {
            return row[variable];
        }

        return row[position];
    }

    getActiveTableRows() {
        if (!this.tableDataSparse || !this.tableDataFull) {
            return null;
        }

        return this.shouldHideEmptyChartSlots()
            ? this.tableDataSparse
            : this.tableDataFull;
    }

    getCapacityMetricSpecs() {
        const minutes = this.chartValueMode === 'minutes' && this.supportsMinutesChartMode();
        if (minutes) {
            return {
                booked: {
                    totalVariable: 'bookedminutes',
                    totalPosition: 4,
                    publicVariable: 'bookedminutes_public',
                    publicPosition: 8,
                },
                planned: {
                    totalVariable: 'plannedminutes',
                    totalPosition: 5,
                    publicVariable: 'plannedminutes_public',
                    publicPosition: 9,
                },
            };
        }

        return {
            booked: {
                totalVariable: 'bookedcount',
                totalPosition: 2,
                publicVariable: 'bookedcount_public',
                publicPosition: 6,
            },
            planned: {
                totalVariable: 'plannedcount',
                totalPosition: 3,
                publicVariable: 'plannedcount_public',
                publicPosition: 7,
            },
        };
    }

    getCapacityChannelLabel() {
        if (this.chartChannelMode === 'public') {
            return 'Internet';
        }
        if (this.chartChannelMode === 'intern_only') {
            return 'nur intern';
        }

        return 'insgesamt';
    }

    getChannelCapacityMetric(row, metric) {
        const specs = this.getCapacityMetricSpecs()[metric];
        const total = Number(this.getCapacityTableRowValue(
            row,
            specs.totalVariable,
            specs.totalPosition
        )) || 0;
        const bookedPublic = Number(this.getCapacityTableRowValue(
            row,
            specs.publicVariable,
            specs.publicPosition
        )) || 0;

        if (this.chartChannelMode === 'public') {
            return bookedPublic;
        }
        if (this.chartChannelMode === 'intern_only') {
            return Math.max(0, total - bookedPublic);
        }

        return total;
    }

    getCapacityTableHeaderLabel(kind) {
        const showMinutes = this.chartValueMode === 'minutes' && this.supportsMinutesChartMode();
        const unit = showMinutes ? 'Minuten' : 'Zeitschlitze';
        const prefix = kind === 'planned' ? 'Geplante' : 'Gebuchte';

        return `${prefix} Kapazität ${this.getCapacityChannelLabel()} (${unit})`;
    }

    syncCapacityTableHeaders() {
        const $table = this.$main.find('.report-board--capacity-table').first();
        if (!$table.length) {
            return;
        }

        $table.find('.report-board--capacity-planned').text(this.getCapacityTableHeaderLabel('planned'));
        $table.find('.report-board--capacity-booked').text(this.getCapacityTableHeaderLabel('booked'));
    }

    formatCapacitySummaryNumber(value) {
        return Number(value).toLocaleString('de-DE', {
            maximumFractionDigits: 0,
        });
    }

    formatCapacitySummaryUtilization(booked, planned) {
        if (planned <= 0) {
            return '0 %';
        }

        const utilization = Math.round((booked / planned) * 1000) / 10;
        return `${utilization.toLocaleString('de-DE', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 1,
        })} %`;
    }

    formatCapacityTableDate(value) {
        if (this.tableIsHourly) {
            return String(value ?? '');
        }

        const datePart = String(value ?? '').substring(0, 10);
        const match = datePart.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!match) {
            return datePart;
        }

        return `${match[3]}.${match[2]}.${match[1]}`;
    }

    renderCapacityTable() {
        const tableRows = this.getActiveTableRows();
        if (!tableRows) {
            return;
        }

        const $tbody = this.$main.find('.report-board--capacity-table tbody').first();
        if (!$tbody.length) {
            return;
        }

        const rows = Array.isArray(tableRows) ? tableRows : Object.values(tableRows);
        if (rows.length === 0) {
            $tbody.empty().append(
                $('<tr/>').append(
                    $('<td/>', { colspan: 4, text: this.tableLabelEmpty }),
                ),
            );
            return;
        }

        let totalPlanned = 0;
        let totalBooked = 0;
        const $fragment = $(document.createDocumentFragment());

        for (const row of rows) {
            const planned = this.getChannelCapacityMetric(row, 'planned');
            const booked = this.getChannelCapacityMetric(row, 'booked');
            totalPlanned += planned;
            totalBooked += booked;
            const utilization = planned > 0
                ? Math.round((booked / planned) * 1000) / 10
                : 0;
            const dateValue = this.getCapacityTableRowValue(row, 'date', 1);

            $fragment.append(
                $('<tr/>').append(
                    $('<td/>', {
                        class: 'colDatumTag statistik',
                        text: this.formatCapacityTableDate(dateValue),
                    }),
                    $('<td/>', { class: 'statistik', text: String(planned) }),
                    $('<td/>', { class: 'statistik', text: String(booked) }),
                    $('<td/>', { class: 'statistik', text: `${utilization} %` }),
                ),
            );
        }

        $fragment.append(
            $('<tr/>', { class: 'report-board--capacity-summary' }).append(
                $('<td/>', {
                    class: 'report-board--summary report-board--capacity-summary-label',
                    text: this.tableLabelSummary,
                }),
                $('<td/>', {
                    class: 'report-board--summary report-board--capacity-summary-value',
                    text: this.formatCapacitySummaryNumber(totalPlanned),
                }),
                $('<td/>', {
                    class: 'report-board--summary report-board--capacity-summary-value',
                    text: this.formatCapacitySummaryNumber(totalBooked),
                }),
                $('<td/>', {
                    class: 'report-board--summary report-board--capacity-summary-value',
                    text: this.formatCapacitySummaryUtilization(totalBooked, totalPlanned),
                    title: 'Gesamtauslastung über den Zeitraum',
                }),
            ),
        );

        $tbody.empty().append($fragment);
    }

    applyChartDataSelection() {
        if (this.chartDataSparse && this.chartDataFull) {
            this.data = this.shouldHideEmptyChartSlots()
                ? this.chartDataSparse
                : this.chartDataFull;
            return;
        }

        this.data = this.chartDataFull;
    }

    downloadChartPng() {
        if (!this.chart) {
            return;
        }

        const sourceCanvas = this.chart.canvas;
        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = sourceCanvas.width;
        exportCanvas.height = sourceCanvas.height;

        const context = exportCanvas.getContext('2d');
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
        context.drawImage(sourceCanvas, 0, 0);

        const link = document.createElement('a');
        link.download = this.getChartDownloadFilename();
        link.href = exportCanvas.toDataURL('image/png', 1);
        link.click();
    }

    formatGermanDateForDisplay(isoDate) {
        const match = String(isoDate).match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!match) {
            return String(isoDate);
        }

        return `${match[3]}.${match[2]}.${match[1]}`;
    }

    formatDateForFilename(isoDate) {
        const match = String(isoDate).match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!match) {
            return String(isoDate).replace(/[^0-9-]/g, '');
        }

        return `${match[1]}-${match[2]}-${match[3]}`;
    }

    getChartDateRangeLabel() {
        if (this.chartDateFrom && this.chartDateTo) {
            return `${this.formatGermanDateForDisplay(this.chartDateFrom)} bis ${this.formatGermanDateForDisplay(this.chartDateTo)}`;
        }

        if (this.chartPeriod) {
            return this.chartPeriod;
        }

        return '';
    }

    getChartDateRangeFilenamePart() {
        if (this.chartDateFrom && this.chartDateTo) {
            return `${this.formatDateForFilename(this.chartDateFrom)}-bis-${this.formatDateForFilename(this.chartDateTo)}`;
        }

        if (this.chartPeriod) {
            return String(this.chartPeriod).replace(/[^0-9-]/g, '');
        }

        return '';
    }

    getChartDateRangeSuffix() {
        const rangePart = this.getChartDateRangeFilenamePart();
        if (!rangePart) {
            return '';
        }

        return `_${rangePart}`;
    }

    getChartDownloadFilename() {
        const suffix = this.chartValueMode === 'minutes' ? '-minuten' : '-zeitschlitze';
        return `terminkapazitaet${suffix}${this.getChartDateRangeSuffix()}.png`;
    }

    syncChartDownloadButton() {
        const $button = this.$main.find('.report-board--chart-download');
        if (!$button.length) {
            return;
        }

        const hasChart = Boolean(this.data && this.data.visualization);
        $button.prop('disabled', !hasChart);
        $button.toggleClass('is-disabled', !hasChart);

        const rangeLabel = this.getChartDateRangeLabel();
        const title = rangeLabel
            ? `Diagramm als Bild herunterladen (${rangeLabel})`
            : 'Diagramm als Bild herunterladen';
        $button.attr('title', title);
        $button.attr('aria-label', title);
    }

    supportsMinutesChartMode() {
        const visualization = this.data && this.data.visualization;
        return Boolean(
            visualization
            && Array.isArray(visualization.ylabelMinutes)
            && visualization.ylabelMinutes.length > 0
        );
    }

    supportsCapacityChannelMode() {
        const visualization = this.data && this.data.visualization;
        if (visualization && visualization.allowCapacityChannel) {
            return true;
        }

        const rows = this.tableDataSparse || this.tableDataFull || (this.data && this.data.data);
        if (!Array.isArray(rows) || rows.length === 0) {
            return false;
        }

        const firstRow = rows[0];
        if (Array.isArray(firstRow)) {
            return firstRow.length > 6;
        }

        return firstRow.bookedcount_public !== undefined;
    }

    initCapacityChannelFromDom() {
        const $select = this.$main.find('.report-board--capacity-channel-select').first();
        if (!$select.length) {
            return;
        }

        const storedValue = window.sessionStorage.getItem(View.CAPACITY_CHANNEL_STORAGE_KEY);
        const channel = View.CAPACITY_CHANNEL_MODES.includes(storedValue) ? storedValue : 'total';
        this.chartChannelMode = channel;
        $select.val(channel);
    }

    setCapacityChannelMode(channel) {
        const normalized = View.CAPACITY_CHANNEL_MODES.includes(channel) ? channel : 'total';
        this.chartChannelMode = normalized;
        this.$main.find('.report-board--capacity-channel-select').first().val(normalized);
        window.sessionStorage.setItem(View.CAPACITY_CHANNEL_STORAGE_KEY, normalized);
        this.syncCapacityChannelSelect();
        this.syncCapacityTableHeaders();
        this.renderChartjs();
        this.renderCapacityTable();
    }

    syncCapacityChannelSelect() {
        const $label = this.$main.find('.report-board--capacity-channel').first();
        if (!$label.length) {
            return;
        }

        if (!this.supportsCapacityChannelMode()) {
            $label.hide();
            return;
        }

        $label.show();
    }

    supportsSparseChartTimeline() {
        if (this.chartDataSparse && this.chartDataFull) {
            return true;
        }

        if (this.tableDataSparse && this.tableDataFull) {
            return true;
        }

        if (this.$main.find('.report-board--chart-sparse').length > 0) {
            return true;
        }

        const visualization = this.data && this.data.visualization;
        return Boolean(
            visualization
            && (
                visualization.allowSparseTimeline
                || this.supportsMinutesChartMode()
            )
        );
    }

    shouldHideEmptyChartSlots() {
        return this.chartHideEmptySlots && this.supportsSparseChartTimeline();
    }

    toggleChartSparseTimeline() {
        if (!this.supportsSparseChartTimeline()) {
            return;
        }
        this.chartHideEmptySlots = !this.chartHideEmptySlots;
        this.applyChartDataSelection();
        this.syncSparseTimelineButton();
        this.renderChartjs();
        this.renderCapacityTable();
    }

    syncSparseTimelineButton() {
        const $button = this.$main.find('.report-board--chart-sparse');
        if (!$button.length) {
            return;
        }
        if (!this.supportsSparseChartTimeline()) {
            $button.hide();
            return;
        }
        $button.show();
        const hideEmpty = this.chartHideEmptySlots;
        $button.attr('aria-pressed', hideEmpty ? 'true' : 'false');
        $button.toggleClass('is-active', hideEmpty);
        $button.attr(
            'title',
            hideEmpty ? 'Leere Zeiten einblenden' : 'Leere Zeiten ausblenden'
        );
        $button.attr(
            'aria-label',
            hideEmpty ? 'Leere Zeiten einblenden' : 'Leere Zeiten ausblenden'
        );
    }

    getActiveYLabels() {
        const visualization = this.data.visualization;
        if (this.chartChannelMode === 'public') {
            if (this.chartValueMode === 'minutes' && this.supportsMinutesChartMode()) {
                return visualization.ylabelMinutesPublic || visualization.ylabelMinutes;
            }
            return visualization.ylabelPublic || visualization.ylabel;
        }
        if (this.chartValueMode === 'minutes' && this.supportsMinutesChartMode()) {
            return visualization.ylabelMinutes;
        }
        return visualization.ylabel;
    }

    getYAxisTitle() {
        if (this.chartValueMode === 'minutes' && this.supportsMinutesChartMode()) {
            return 'Minuten';
        }
        return 'Zeitschlitze';
    }

    toggleChartValueMode() {
        if (!this.supportsMinutesChartMode()) {
            return;
        }
        this.chartValueMode = this.chartValueMode === 'minutes' ? 'slots' : 'minutes';
        this.syncChartModeButton();
        this.syncCapacityTableHeaders();
        this.renderChartjs();
        this.renderCapacityTable();
    }

    syncChartModeButton() {
        const $button = this.$main.find('.report-board--chart-minutes');
        if (!$button.length) {
            return;
        }
        if (!this.supportsMinutesChartMode()) {
            $button.hide();
            return;
        }
        $button.show();
        const showMinutes = this.chartValueMode === 'minutes';
        $button.attr('aria-pressed', showMinutes ? 'true' : 'false');
        $button.toggleClass('is-active', showMinutes);
        $button.attr(
            'title',
            showMinutes ? 'Als Terminanzahl anzeigen' : 'Als Slotzeit in Minuten anzeigen'
        );
        $button.attr(
            'aria-label',
            showMinutes ? 'Als Terminanzahl anzeigen' : 'Als Slotzeit in Minuten anzeigen'
        );
    }

    initAutoRefreshFromDom() {
        const $select = this.$main.find('.report-board--auto-refresh-interval').first();
        if (!$select.length) {
            return;
        }

        const storedValue = window.sessionStorage.getItem(View.AUTO_REFRESH_STORAGE_KEY);
        const storedSeconds = storedValue === null ? 0 : Number(storedValue);
        const seconds = View.AUTO_REFRESH_INTERVALS_SECONDS.includes(storedSeconds)
            ? storedSeconds
            : 0;

        this.setAutoRefreshInterval(seconds, false);
    }

    syncAutoRefreshSelect() {
        const $select = this.$main.find('.report-board--auto-refresh-interval').first();
        if (!$select.length) {
            return;
        }

        const seconds = this.autoRefreshIntervalMs / 1000;
        $select.val(String(seconds));
    }

    setAutoRefreshInterval(seconds, persist = true) {
        const normalized = View.AUTO_REFRESH_INTERVALS_SECONDS.includes(seconds) ? seconds : 0;

        this.clearAutoRefreshTimer();
        this.autoRefreshIntervalMs = normalized * 1000;
        this.syncAutoRefreshSelect();

        if (persist) {
            window.sessionStorage.setItem(
                View.AUTO_REFRESH_STORAGE_KEY,
                String(normalized)
            );
        }

        if (this.autoRefreshIntervalMs <= 0) {
            return;
        }

        this.autoRefreshTimer = window.setInterval(() => {
            if (document.hidden) {
                return;
            }

            const $button = this.$main.find('.report-board--refresh');
            if ($button.prop('disabled')) {
                return;
            }

            this.refreshReportContent({ silent: true });
        }, this.autoRefreshIntervalMs);
    }

    clearAutoRefreshTimer() {
        if (this.autoRefreshTimer) {
            window.clearInterval(this.autoRefreshTimer);
            this.autoRefreshTimer = null;
        }
    }

    canSoftRefresh() {
        return Boolean(
            this.chart
            && this.chartDataFull
            && this.$main.find('.chartist canvas').length
        );
    }

    parseRefreshPayload($newBoard) {
        const hasChartPayload = $newBoard.find('.chartist, script.report-board--chart-data, script.report-board--chart-data-sparse').length > 0;
        if (!hasChartPayload) {
            return null;
        }

        const payload = {};
        const sparseData = this.readJsonPayload(
            $newBoard,
            'script.report-board--chart-data-sparse',
            'data-chartist-sparse',
            'refresh-chart-sparse'
        );
        const fullData = this.readJsonPayload(
            $newBoard,
            'script.report-board--chart-data-full',
            'data-chartist-full',
            'refresh-chart-full'
        );
        const chartData = this.readJsonPayload(
            $newBoard,
            'script.report-board--chart-data',
            'data-chartist',
            'refresh-chart'
        );

        if (sparseData && fullData) {
            payload.chartDataSparse = sparseData;
            payload.chartDataFull = fullData;
        } else if (chartData) {
            payload.chartDataSparse = null;
            payload.chartDataFull = chartData;
        } else {
            return null;
        }

        const $table = $newBoard.find('.report-board--capacity-table').first();
        const tableSparseData = this.readJsonPayload(
            $newBoard,
            'script.report-board--table-data-sparse',
            'data-table-sparse',
            'refresh-table-sparse'
        );
        const tableFullData = this.readJsonPayload(
            $newBoard,
            'script.report-board--table-data-full',
            'data-table-full',
            'refresh-table-full'
        );
        if (tableSparseData && tableFullData) {
            payload.tableDataSparse = tableSparseData;
            payload.tableDataFull = tableFullData;
        }

        const summaryLabel = $table.attr('data-label-summary');
        if (summaryLabel) {
            payload.tableLabelSummary = summaryLabel;
        }

        const $slotHint = $newBoard.find('.report-board--chart-hint-slot-times');
        if ($slotHint.length) {
            payload.slotTimeHint = $slotHint.text();
        }

        return payload;
    }

    applySoftRefreshPayload(payload) {
        if (!payload || !payload.chartDataFull) {
            return false;
        }

        try {
            this.chartDataSparse = payload.chartDataSparse ?? null;
            this.chartDataFull = payload.chartDataFull;

            if (payload.tableDataSparse && payload.tableDataFull) {
                this.tableDataSparse = payload.tableDataSparse;
                this.tableDataFull = payload.tableDataFull;
            }

            if (payload.tableLabelSummary) {
                this.tableLabelSummary = payload.tableLabelSummary;
            }

            this.applyChartDataSelection();
            this.updateChartDataInPlace();
            this.syncCapacityTableHeaders();
            this.renderCapacityTable();

            if (payload.slotTimeHint) {
                this.$main.find('.report-board--chart-hint-slot-times').text(payload.slotTimeHint);
            }

            return true;
        } catch (error) {
            console.error('Soft report refresh failed', error);
            return false;
        }
    }

    buildChartDatasets() {
        const colorlist = [
            '#008cca',
            '#ffacaa',
            '#d0eaca',
            '#c2c2c2',
            '#efc10f',
        ];
        const datasets = [];

        if (this.supportsCapacityChannelMode() && this.chartChannelMode === 'intern_only') {
            const series = [
                { metric: 'booked', kind: 'Gebuchte' },
                { metric: 'planned', kind: 'Geplante' },
            ];

            for (const entry of series) {
                const lineColor = colorlist.shift();
                datasets.push({
                    label: this.getCapacityTableHeaderLabel(entry.metric === 'planned' ? 'planned' : 'booked'),
                    data: this.data.data.map((row) => this.getChannelCapacityMetric(row, entry.metric)),
                    borderColor: lineColor,
                    pointBackgroundColor: lineColor,
                    pointBorderColor: lineColor,
                    fill: false,
                });
            }

            return datasets;
        }

        for (const datalabel of this.getActiveYLabels()) {
            const dataset = {};
            dataset.label = this.getLabelInfo(datalabel).description;
            dataset.data = this.getListByLabel(datalabel);
            const lineColor = colorlist.shift();
            dataset.borderColor = lineColor;
            dataset.pointBackgroundColor = lineColor;
            dataset.pointBorderColor = lineColor;
            dataset.fill = false;
            datasets.push(dataset);
        }

        return datasets;
    }

    getChartAnimationOptions() {
        const disabled = { duration: 0 };
        return {
            animation: false,
            animations: {
                colors: disabled,
                numbers: disabled,
                tension: disabled,
                x: disabled,
                y: disabled,
            },
            transitions: {
                active: { animation: disabled },
                resize: { animation: disabled },
                show: { animations: { colors: disabled, x: disabled, y: disabled } },
                hide: { animations: { colors: disabled, x: disabled, y: disabled } },
            },
        };
    }

    patchArrayInPlace(target, source) {
        if (target.length === source.length) {
            for (let index = 0; index < source.length; index += 1) {
                target[index] = source[index];
            }
            return;
        }

        target.length = 0;
        target.push(...source);
    }

    hasChartDataChanged(nextLabels, nextDatasets) {
        const chart = this.chart;
        if (!chart) {
            return true;
        }

        if (chart.data.labels.length !== nextLabels.length) {
            return true;
        }

        for (let index = 0; index < nextLabels.length; index += 1) {
            if (chart.data.labels[index] !== nextLabels[index]) {
                return true;
            }
        }

        if (chart.data.datasets.length !== nextDatasets.length) {
            return true;
        }

        for (let datasetIndex = 0; datasetIndex < nextDatasets.length; datasetIndex += 1) {
            const current = chart.data.datasets[datasetIndex];
            const next = nextDatasets[datasetIndex];
            if (!current || current.label !== next.label) {
                return true;
            }

            if (current.data.length !== next.data.length) {
                return true;
            }

            for (let valueIndex = 0; valueIndex < next.data.length; valueIndex += 1) {
                if (Number(current.data[valueIndex]) !== Number(next.data[valueIndex])) {
                    return true;
                }
            }
        }

        return false;
    }

    patchChartDataInPlace(nextLabels, nextDatasets) {
        const chart = this.chart;
        this.patchArrayInPlace(chart.data.labels, nextLabels);

        nextDatasets.forEach((source, datasetIndex) => {
            let target = chart.data.datasets[datasetIndex];
            if (!target) {
                chart.data.datasets[datasetIndex] = { ...source, data: source.data.slice() };
                return;
            }

            target.label = source.label;
            target.borderColor = source.borderColor;
            target.pointBackgroundColor = source.pointBackgroundColor;
            target.pointBorderColor = source.pointBorderColor;
            target.fill = source.fill;

            if (!Array.isArray(target.data)) {
                target.data = source.data.slice();
                return;
            }

            this.patchArrayInPlace(target.data, source.data);
        });

        chart.data.datasets.length = nextDatasets.length;
    }

    updateChartDataInPlace() {
        if (!this.chart || !this.data || !this.data.visualization) {
            this.renderChartjs();
            return;
        }

        const labels = this.getListByLabel(this.data.visualization.xlabel[0]);
        const datasets = this.buildChartDatasets();

        if (!this.hasChartDataChanged(labels, datasets)) {
            return;
        }

        const maxY = this.getMaxYValue(datasets);
        this.patchChartDataInPlace(labels, datasets);

        if (this.chart.options.scales?.y) {
            const nextSuggestedMax = maxY === 0 ? 1 : undefined;
            if (this.chart.options.scales.y.suggestedMax !== nextSuggestedMax) {
                this.chart.options.scales.y.suggestedMax = nextSuggestedMax;
            }
            this.chart.options.scales.y.title.text = this.getYAxisTitle();
        }

        if (typeof this.chart.stop === 'function') {
            this.chart.stop();
        }
        this.chart._animationsDisabled = true;
        this.chart.update('none');
    }

    buildChartOptions(labels, maxY) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            ...this.getChartAnimationOptions(),
            interaction: {
                mode: 'index',
                intersect: false,
            },
            datasets: {
                line: {
                    clip: false,
                },
            },
            elements: {
                point: {
                    radius: 3,
                    hoverRadius: 5,
                    hitRadius: 10,
                },
                line: {
                    tension: 0.25,
                },
            },
            scales: {
                x: {
                    ticks: this.getXAxisTickOptions(labels),
                },
                y: {
                    beginAtZero: true,
                    min: 0,
                    suggestedMax: maxY === 0 ? 1 : undefined,
                    grace: '5%',
                    title: {
                        display: this.supportsMinutesChartMode(),
                        text: this.getYAxisTitle(),
                    },
                },
            },
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        boxWidth: 12,
                        usePointStyle: false,
                    },
                },
                tooltip: {
                    mode: 'index',
                },
            },
        };
    }

    async refreshReportContent(options = {}) {
        const silent = options.silent === true;
        const $board = this.$main.find('.board').first();
        const $button = this.$main.find('.report-board--refresh');
        if (!$board.length || this.refreshInFlight || (!silent && $button.prop('disabled'))) {
            return;
        }

        this.refreshInFlight = true;

        $button.find('i').addClass('fa-spin');
        if (!silent) {
            $button.prop('disabled', true);
        }

        try {
            const refreshUrl = new URL(window.location.href);
            refreshUrl.searchParams.set('_refresh', String(Date.now()));
            const response = await fetch(refreshUrl.toString(), {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`Refresh failed (${response.status})`);
            }

            const html = await response.text();
            const $parsed = $('<div>').append($.parseHTML(html, document, true));
            const $newBoard = $parsed.find('.capacity-report .board').first();

            if (!$newBoard.length) {
                throw new Error('Board markup not found in response');
            }

            if (this.canSoftRefresh()) {
                const payload = this.parseRefreshPayload($newBoard);
                if (payload && this.applySoftRefreshPayload(payload)) {
                    return;
                }
                if (silent) {
                    return;
                }
            }

            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }

            $board.replaceWith($newBoard);
            this.initCapacityTableSettings();
            this.initCapacityTableDataFromDom();
            this.initChartFromDom();
            this.initCapacityChannelFromDom();
            this.syncCapacityChannelSelect();
            this.syncCapacityTableHeaders();
            this.renderCapacityTable();
            this.syncAutoRefreshSelect();
        } catch (error) {
            console.error('Report refresh failed', error);
            if (!silent) {
                window.location.reload();
            }
        } finally {
            this.refreshInFlight = false;
            const $refreshButton = this.$main.find('.report-board--refresh');
            $refreshButton.find('i').removeClass('fa-spin');
            if (!silent) {
                $refreshButton.prop('disabled', false);
            }
        }
    }

    getRowValue(row, info) {
        if (row == null || info == null) {
            return undefined;
        }
        if (typeof row === 'object') {
            if (info.variable != null && row[info.variable] !== undefined) {
                return row[info.variable];
            }
        }
        return row[info.position];
    }

    reduceToField(info) {
        return (list, items) => {
            list.push(this.getRowValue(items, info));
            return list;
        }
    }

    getLabelInfo(label) {
        for (const info of this.data.dictionary) {
            if (info.variable == label) {
                return info;
            }
        }
        throw "CapacityReport: Label " + label + " not found"
    }

    getListByLabel(label) {
        const info = this.getLabelInfo(label);
        const list = this.data.data.reduce(this.reduceToField(info).bind(this), []);
        return list;
    }

    getMaxYValue(datasets) {
        let max = 0;
        for (const dataset of datasets) {
            for (const value of dataset.data) {
                const number = Number(value);
                if (!Number.isNaN(number) && number > max) {
                    max = number;
                }
            }
        }
        return max;
    }

    getChartLabelIntervalHours() {
        const visualization = this.data.visualization;
        if (!visualization || visualization.labelIntervalHours == null) {
            return null;
        }
        const interval = Number(visualization.labelIntervalHours);
        return Number.isNaN(interval) || interval < 1 ? null : interval;
    }

    usesSparseChartData() {
        return this.shouldHideEmptyChartSlots() && Boolean(this.chartDataSparse);
    }

    getCategoryLabelIndex(tickValue) {
        const index = Number(tickValue);
        return Number.isInteger(index) && index >= 0 ? index : null;
    }

    shouldShowXTickLabel(label, index, labels) {
        const intervalHours = this.getChartLabelIntervalHours();

        if (this.usesSparseChartData() || intervalHours === null) {
            if (labels.length <= 15) {
                return true;
            }
            const step = Math.max(1, Math.ceil(labels.length / 15));
            return index % step === 0 || index === labels.length - 1;
        }

        const match = String(label).match(/^(\d{4}-\d{2}-\d{2}) (\d{2}):00$/);
        if (!match) {
            return index === 0 || index === labels.length - 1;
        }

        const hour = parseInt(match[2], 10);
        return hour % intervalHours === 0;
    }

    getXAxisTickOptions(labels) {
        const labelIntervalHours = this.getChartLabelIntervalHours();
        const useTickCallback = this.usesSparseChartData()
            || labelIntervalHours !== null
            || labels.length > 31;

        if (!useTickCallback) {
            return {
                autoSkip: labels.length > 31,
                maxRotation: 45,
                minRotation: 0
            };
        }

        const self = this;
        return {
            autoSkip: false,
            maxRotation: 45,
            minRotation: 0,
            callback(tickValue) {
                const index = self.getCategoryLabelIndex(tickValue);
                if (index === null || index >= labels.length) {
                    return '';
                }
                const label = labels[index];
                if (!self.shouldShowXTickLabel(label, index, labels)) {
                    return '';
                }
                return label;
            }
        };
    }

    renderChartjs() {
        if (!this.data || !this.data.visualization) {
            return;
        }

        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }

        const labels = this.getListByLabel(this.data.visualization.xlabel[0]);
        const datasets = this.buildChartDatasets();
        const maxY = this.getMaxYValue(datasets);
        this.$.find(".chartist").html('<canvas></canvas>&nbsp;');
        this.$.find(".chartist").css({
            "position": "relative",
            "width": "100%",
            "height": "550px"
        });
        const $canvas = this.$.find(".chartist canvas");
        const canvascontext = $canvas[0].getContext('2d');
        //console.log(datasets);
        this.chart = new Chart(canvascontext, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: this.buildChartOptions(labels, maxY),
        });
        this.chart._animationsDisabled = true;
        this.syncChartDownloadButton();
        this.syncSparseTimelineButton();
    }
}

export default View;

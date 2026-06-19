import $ from 'jquery';
import { readJsonPayload, getCapacityTableRowValue } from './exchangeData';
import {
    getChannelCapacityMetric,
    getCapacityTableHeaderLabel,
} from './capacityMetrics';
import {
    formatCapacitySummaryNumber,
    formatCapacitySummaryUtilization,
    formatCapacityTableDate,
} from './formatting';

export default class CapacityTable {
    constructor(view) {
        this.view = view;
    }

    supportsMinutesChartMode() {
        return this.view.chartController.supportsMinutesChartMode();
    }

    initSettingsFromDom() {
        const $table = this.view.$main.find('.report-board--capacity-table').first();
        if (!$table.length) {
            return;
        }

        this.view.tableIsHourly = $table.attr('data-table-is-hourly') === '1';
        this.view.tableLabelSummary = $table.attr('data-label-summary') || 'Gesamt';
        this.view.tableLabelEmpty = $table.attr('data-label-empty') || 'Es wurden keine Daten gefunden';
    }

    initDataFromDom() {
        const $table = this.view.$main.find('.report-board--capacity-table').first();
        if (!$table.length) {
            return;
        }

        const sparseData = readJsonPayload(
            this.view.$main,
            'script.report-board--table-data-sparse',
            'data-table-sparse',
            'table-sparse'
        );
        const fullData = readJsonPayload(
            this.view.$main,
            'script.report-board--table-data-full',
            'data-table-full',
            'table-full'
        );

        if (!sparseData || !fullData) {
            return;
        }

        this.view.tableDataSparse = sparseData;
        this.view.tableDataFull = fullData;
    }

    getActiveRows() {
        if (!this.view.tableDataSparse || !this.view.tableDataFull) {
            return null;
        }

        return this.view.chartController.shouldHideEmptyChartSlots()
            ? this.view.tableDataSparse
            : this.view.tableDataFull;
    }

    syncHeaders() {
        const $table = this.view.$main.find('.report-board--capacity-table').first();
        if (!$table.length) {
            return;
        }

        const supportsMinutes = this.supportsMinutesChartMode();
        $table.find('.report-board--capacity-planned').text(
            getCapacityTableHeaderLabel(
                'planned',
                this.view.chartChannelMode,
                this.view.chartValueMode,
                supportsMinutes
            )
        );
        $table.find('.report-board--capacity-booked').text(
            getCapacityTableHeaderLabel(
                'booked',
                this.view.chartChannelMode,
                this.view.chartValueMode,
                supportsMinutes
            )
        );
    }

    render() {
        const tableRows = this.getActiveRows();
        if (!tableRows) {
            return;
        }

        const $tbody = this.view.$main.find('.report-board--capacity-table tbody').first();
        if (!$tbody.length) {
            return;
        }

        const rows = Array.isArray(tableRows) ? tableRows : Object.values(tableRows);
        if (rows.length === 0) {
            $tbody.empty().append(
                $('<tr/>').append(
                    $('<td/>', { colspan: 4, text: this.view.tableLabelEmpty }),
                ),
            );
            return;
        }

        let totalPlanned = 0;
        let totalBooked = 0;
        const $fragment = $(document.createDocumentFragment());
        const supportsMinutes = this.supportsMinutesChartMode();

        for (const row of rows) {
            const planned = getChannelCapacityMetric(
                row,
                'planned',
                this.view.chartChannelMode,
                this.view.chartValueMode,
                supportsMinutes
            );
            const booked = getChannelCapacityMetric(
                row,
                'booked',
                this.view.chartChannelMode,
                this.view.chartValueMode,
                supportsMinutes
            );
            totalPlanned += planned;
            totalBooked += booked;
            const utilization = planned > 0
                ? Math.round((booked / planned) * 1000) / 10
                : 0;
            const dateValue = getCapacityTableRowValue(row, 'date', 1);

            $fragment.append(
                $('<tr/>').append(
                    $('<td/>', {
                        class: 'colDatumTag statistik',
                        text: formatCapacityTableDate(dateValue, this.view.tableIsHourly),
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
                    text: this.view.tableLabelSummary,
                }),
                $('<td/>', {
                    class: 'report-board--summary report-board--capacity-summary-value',
                    text: formatCapacitySummaryNumber(totalPlanned),
                }),
                $('<td/>', {
                    class: 'report-board--summary report-board--capacity-summary-value',
                    text: formatCapacitySummaryNumber(totalBooked),
                }),
                $('<td/>', {
                    class: 'report-board--summary report-board--capacity-summary-value',
                    text: formatCapacitySummaryUtilization(totalBooked, totalPlanned),
                    title: 'Gesamtauslastung über den Zeitraum',
                }),
            ),
        );

        $tbody.empty().append($fragment);
    }
}

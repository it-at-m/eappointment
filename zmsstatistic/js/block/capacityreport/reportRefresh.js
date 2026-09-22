import $ from 'jquery';
import { readJsonPayload, readPairedJsonPayload } from './exchangeData';

export default class ReportRefresh {
    constructor(view) {
        this.view = view;
    }

    canSoftRefresh() {
        return Boolean(
            this.view.chart
            && this.view.chartDataFull
            && this.view.$main.find('.chartist canvas').length
        );
    }

    parsePayload($newBoard) {
        const hasChartPayload = $newBoard.find(
            '.chartist, script.report-board--chart-data, script.report-board--chart-data-sparse'
        ).length > 0;
        if (!hasChartPayload) {
            return null;
        }

        const payload = this.readRefreshChartPayload($newBoard);
        if (!payload) {
            return null;
        }

        this.assignPairedTablePayload($newBoard, payload);
        this.assignHourlyRefreshPayload($newBoard, payload);
        this.assignRefreshMeta($newBoard, payload);
        return payload;
    }

    readRefreshChartPayload($newBoard) {
        const paired = readPairedJsonPayload(
            $newBoard,
            ['script.report-board--chart-data-sparse', 'data-chartist-sparse', 'refresh-chart-sparse'],
            ['script.report-board--chart-data-full', 'data-chartist-full', 'refresh-chart-full']
        );
        if (paired) {
            return {
                chartDataSparse: paired.sparse,
                chartDataFull: paired.full,
            };
        }

        const chartData = readJsonPayload(
            $newBoard,
            'script.report-board--chart-data',
            'data-chartist',
            'refresh-chart'
        );
        if (!chartData) {
            return null;
        }

        return {
            chartDataSparse: null,
            chartDataFull: chartData,
        };
    }

    assignPairedTablePayload($newBoard, payload) {
        const paired = readPairedJsonPayload(
            $newBoard,
            ['script.report-board--table-data-sparse', 'data-table-sparse', 'refresh-table-sparse'],
            ['script.report-board--table-data-full', 'data-table-full', 'refresh-table-full']
        );
        if (!paired) {
            return;
        }

        payload.dailyTableDataSparse = paired.sparse;
        payload.dailyTableDataFull = paired.full;
        payload.tableDataSparse = paired.sparse;
        payload.tableDataFull = paired.full;
    }

    assignHourlyRefreshPayload($newBoard, payload) {
        const hourlyChart = readPairedJsonPayload(
            $newBoard,
            ['script.report-board--chart-data-sparse-hourly', 'data-chartist-sparse-hourly', 'refresh-chart-sparse-hourly'],
            ['script.report-board--chart-data-full-hourly', 'data-chartist-full-hourly', 'refresh-chart-full-hourly']
        );
        if (hourlyChart) {
            payload.hourlyChartDataSparse = hourlyChart.sparse;
            payload.hourlyChartDataFull = hourlyChart.full;
        }

        const hourlyTable = readPairedJsonPayload(
            $newBoard,
            ['script.report-board--table-data-sparse-hourly', 'data-table-sparse-hourly', 'refresh-table-sparse-hourly'],
            ['script.report-board--table-data-full-hourly', 'data-table-full-hourly', 'refresh-table-full-hourly']
        );
        if (hourlyTable) {
            payload.hourlyTableDataSparse = hourlyTable.sparse;
            payload.hourlyTableDataFull = hourlyTable.full;
        }
    }

    assignRefreshMeta($newBoard, payload) {
        const summaryLabel = $newBoard.find('.report-board--capacity-table').first().attr('data-label-summary');
        if (summaryLabel) {
            payload.tableLabelSummary = summaryLabel;
        }

        const $slotHint = $newBoard.find('.report-board--chart-hint-slot-times');
        if ($slotHint.length) {
            payload.slotTimeHint = $slotHint.text();
        }
    }

    applySoftPayload(payload) {
        if (!payload?.chartDataFull) {
            return false;
        }

        try {
            this.assignSoftChartData(payload);
            this.assignSoftTableData(payload);
            this.rerenderAfterSoftPayload();
            this.applySlotTimeHint(payload);
            return true;
        } catch (error) {
            console.error('Soft report refresh failed', error);
            return false;
        }
    }

    assignSoftChartData(payload) {
        this.view.dailyChartDataSparse = payload.chartDataSparse ?? null;
        this.view.dailyChartDataFull = payload.chartDataFull;
        this.view.chartDataSparse = payload.chartDataSparse ?? null;
        this.view.chartDataFull = payload.chartDataFull;

        if (payload.hourlyChartDataSparse && payload.hourlyChartDataFull) {
            this.view.hourlyChartDataSparse = payload.hourlyChartDataSparse;
            this.view.hourlyChartDataFull = payload.hourlyChartDataFull;
            return;
        }

        this.view.hourlyChartDataSparse = null;
        this.view.hourlyChartDataFull = null;
    }

    assignSoftTableData(payload) {
        if (payload.dailyTableDataSparse && payload.dailyTableDataFull) {
            this.view.dailyTableDataSparse = payload.dailyTableDataSparse;
            this.view.dailyTableDataFull = payload.dailyTableDataFull;
            this.view.tableDataSparse = payload.tableDataSparse;
            this.view.tableDataFull = payload.tableDataFull;
        } else if (payload.tableDataSparse && payload.tableDataFull) {
            this.view.tableDataSparse = payload.tableDataSparse;
            this.view.tableDataFull = payload.tableDataFull;
        }

        if (payload.hourlyTableDataSparse && payload.hourlyTableDataFull) {
            this.view.hourlyTableDataSparse = payload.hourlyTableDataSparse;
            this.view.hourlyTableDataFull = payload.hourlyTableDataFull;
        } else {
            this.view.hourlyTableDataSparse = null;
            this.view.hourlyTableDataFull = null;
        }

        if (payload.tableLabelSummary) {
            this.view.tableLabelSummary = payload.tableLabelSummary;
        }
    }

    rerenderAfterSoftPayload() {
        const previousGranularity = this.view.chartGranularity;
        this.view.chartController.applyGranularity();
        this.view.chartController.syncGranularitySelect();
        this.view.chartController.syncTableDownloadLink();
        if (previousGranularity !== this.view.chartGranularity) {
            this.view.chartController.render();
        } else {
            this.view.chartController.updateInPlace();
        }
        this.view.tableController.syncHeaders();
        this.view.tableController.render();
    }

    applySlotTimeHint(payload) {
        if (!payload.slotTimeHint) {
            return;
        }
        this.view.$main.find('.report-board--chart-hint-slot-times').text(payload.slotTimeHint);
    }

    // eslint-disable-next-line complexity
    async refresh(options = {}) {
        const silent = options.silent === true;
        const $board = this.view.$main.find('.board').first();
        const $button = this.view.$main.find('.report-board--refresh');
        if (!$board.length || this.view.refreshInFlight || (!silent && $button.prop('disabled'))) {
            return;
        }

        this.view.refreshInFlight = true;

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
                const payload = this.parsePayload($newBoard);
                if (payload && this.applySoftPayload(payload)) {
                    return;
                }
                if (silent) {
                    return;
                }
            }

            this.view.chartController.destroy();

            $board.replaceWith($newBoard);
            this.view.tableController.initSettingsFromDom();
            this.view.tableController.initDataFromDom();
            this.view.chartController.initFromDom();
            this.view.chartController.initChannelFromDom();
            this.view.chartController.syncChannelSelect();
            this.view.tableController.syncHeaders();
            this.view.tableController.render();
            this.view.autoRefreshController.syncSelect();
        } catch (error) {
            console.error('Report refresh failed', error);
            if (!silent) {
                window.location.reload();
            }
        } finally {
            this.view.refreshInFlight = false;
            const $refreshButton = this.view.$main.find('.report-board--refresh');
            $refreshButton.find('i').removeClass('fa-spin');
            if (!silent) {
                $refreshButton.prop('disabled', false);
            }
        }
    }
}

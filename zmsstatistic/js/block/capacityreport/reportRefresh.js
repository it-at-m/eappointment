import $ from 'jquery';
import { readJsonPayload } from './exchangeData';

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
        const hasChartPayload = $newBoard.find('.chartist, script.report-board--chart-data, script.report-board--chart-data-sparse').length > 0;
        if (!hasChartPayload) {
            return null;
        }

        const payload = {};
        const sparseData = readJsonPayload(
            $newBoard,
            'script.report-board--chart-data-sparse',
            'data-chartist-sparse',
            'refresh-chart-sparse'
        );
        const fullData = readJsonPayload(
            $newBoard,
            'script.report-board--chart-data-full',
            'data-chartist-full',
            'refresh-chart-full'
        );
        const chartData = readJsonPayload(
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
        const tableSparseData = readJsonPayload(
            $newBoard,
            'script.report-board--table-data-sparse',
            'data-table-sparse',
            'refresh-table-sparse'
        );
        const tableFullData = readJsonPayload(
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

    applySoftPayload(payload) {
        if (!payload || !payload.chartDataFull) {
            return false;
        }

        try {
            this.view.chartDataSparse = payload.chartDataSparse ?? null;
            this.view.chartDataFull = payload.chartDataFull;

            if (payload.tableDataSparse && payload.tableDataFull) {
                this.view.tableDataSparse = payload.tableDataSparse;
                this.view.tableDataFull = payload.tableDataFull;
            }

            if (payload.tableLabelSummary) {
                this.view.tableLabelSummary = payload.tableLabelSummary;
            }

            this.view.chartController.applyDataSelection();
            this.view.chartController.updateInPlace();
            this.view.tableController.syncHeaders();
            this.view.tableController.render();

            if (payload.slotTimeHint) {
                this.view.$main.find('.report-board--chart-hint-slot-times').text(payload.slotTimeHint);
            }

            return true;
        } catch (error) {
            console.error('Soft report refresh failed', error);
            return false;
        }
    }

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

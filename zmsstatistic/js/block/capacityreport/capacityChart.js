import Chart from 'chart.js/auto';
import { readJsonPayload, getListByLabel, getLabelInfo } from './exchangeData';
import {
    getCapacityTableHeaderLabel,
    supportsMinutesChartMode,
    supportsCapacityChannelMode,
    getActiveYLabels,
    getChartDatasetYLabels,
    getYAxisTitle,
    getChannelCapacityMetric,
} from './capacityMetrics';
import {
    getChartDateRangeLabel,
    getChartDownloadFilename,
    syncCapacityTableDownloadHref,
} from './formatting';
import { CAPACITY_CHANNEL_MODES, CAPACITY_CHANNEL_STORAGE_KEY } from './constants';

export default class CapacityChart {
    constructor(view) {
        this.view = view;
    }

    initSparseTimelineFromDom() {
        const $button = this.view.$main.find('.report-board--chart-sparse');
        if (!$button.length) {
            return;
        }
        this.view.chartHideEmptySlots = $button.attr('aria-pressed') !== 'false';
    }

    initFromDom() {
        const $chartist = this.view.$main.find('.chartist').first();
        if (!$chartist.length) {
            return;
        }

        const sparseData = readJsonPayload(
            this.view.$main,
            'script.report-board--chart-data-sparse',
            'data-chartist-sparse',
            'chart-sparse'
        );
        const fullData = readJsonPayload(
            this.view.$main,
            'script.report-board--chart-data-full',
            'data-chartist-full',
            'chart-full'
        );
        const chartData = readJsonPayload(
            this.view.$main,
            'script.report-board--chart-data',
            'data-chartist',
            'chart'
        );

        if (!sparseData && !fullData && !chartData) {
            return;
        }

        $chartist.text('[Initializing chart...]');
        this.view.chartPeriod = $chartist.attr('data-chart-period') || '';
        this.view.chartDateFrom = $chartist.attr('data-chart-date-from') || '';
        this.view.chartDateTo = $chartist.attr('data-chart-date-to') || '';

        if (sparseData && fullData) {
            this.view.chartDataSparse = sparseData;
            this.view.chartDataFull = fullData;
        } else if (chartData) {
            this.view.chartDataSparse = null;
            this.view.chartDataFull = chartData;
        } else {
            $chartist.text('Diagrammdaten konnten nicht geladen werden.');
            console.error('Capacity report: incomplete chart payload', {
                sparseData: Boolean(sparseData),
                fullData: Boolean(fullData),
                chartData: Boolean(chartData),
            });
            return;
        }

        this.applyDataSelection();
        this.syncModeButton();
        this.syncSparseTimelineButton();
        this.syncDownloadButton();
        this.syncTableDownloadLink();
        this.render();
    }

    initChannelFromDom() {
        const $select = this.view.$main.find('.report-board--capacity-channel-select').first();
        if (!$select.length) {
            return;
        }

        const storedValue = window.sessionStorage.getItem(CAPACITY_CHANNEL_STORAGE_KEY);
        const channel = CAPACITY_CHANNEL_MODES.includes(storedValue) ? storedValue : 'total';
        this.view.chartChannelMode = channel;
        $select.val(channel);
    }

    setChannelMode(channel) {
        const normalized = CAPACITY_CHANNEL_MODES.includes(channel) ? channel : 'total';
        this.view.chartChannelMode = normalized;
        this.view.$main.find('.report-board--capacity-channel-select').first().val(normalized);
        window.sessionStorage.setItem(CAPACITY_CHANNEL_STORAGE_KEY, normalized);
        this.syncChannelSelect();
        this.view.tableController.syncHeaders();
        this.render();
        this.view.tableController.render();
    }

    syncChannelSelect() {
        const $label = this.view.$main.find('.report-board--capacity-channel').first();
        if (!$label.length) {
            return;
        }

        if (!this.supportsCapacityChannelMode()) {
            $label.hide();
            return;
        }

        $label.show();
    }

    supportsMinutesChartMode() {
        return supportsMinutesChartMode(this.view.data);
    }

    supportsCapacityChannelMode() {
        return supportsCapacityChannelMode({
            data: this.view.data,
            tableDataSparse: this.view.tableDataSparse,
            tableDataFull: this.view.tableDataFull,
        });
    }

    supportsSparseChartTimeline() {
        if (this.view.chartDataSparse && this.view.chartDataFull) {
            return true;
        }

        if (this.view.tableDataSparse && this.view.tableDataFull) {
            return true;
        }

        if (this.view.$main.find('.report-board--chart-sparse').length > 0) {
            return true;
        }

        const visualization = this.view.data && this.view.data.visualization;
        return Boolean(
            visualization
            && (
                visualization.allowSparseTimeline
                || this.supportsMinutesChartMode()
            )
        );
    }

    shouldHideEmptyChartSlots() {
        return this.view.chartHideEmptySlots && this.supportsSparseChartTimeline();
    }

    applyDataSelection() {
        if (this.view.chartDataSparse && this.view.chartDataFull) {
            this.view.data = this.shouldHideEmptyChartSlots()
                ? this.view.chartDataSparse
                : this.view.chartDataFull;
            return;
        }

        this.view.data = this.view.chartDataFull;
    }

    toggleSparseTimeline() {
        if (!this.supportsSparseChartTimeline()) {
            return;
        }
        this.view.chartHideEmptySlots = !this.view.chartHideEmptySlots;
        this.applyDataSelection();
        this.syncSparseTimelineButton();
        this.render();
        this.view.tableController.render();
    }

    toggleValueMode() {
        if (!this.supportsMinutesChartMode()) {
            return;
        }
        this.view.chartValueMode = this.view.chartValueMode === 'minutes' ? 'slots' : 'minutes';
        this.syncModeButton();
        this.syncTableDownloadLink();
        this.view.tableController.syncHeaders();
        this.render();
        this.view.tableController.render();
    }

    syncSparseTimelineButton() {
        const $button = this.view.$main.find('.report-board--chart-sparse');
        if (!$button.length) {
            return;
        }
        if (!this.supportsSparseChartTimeline()) {
            $button.hide();
            return;
        }
        $button.show();
        const hideEmpty = this.view.chartHideEmptySlots;
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

    syncModeButton() {
        const $button = this.view.$main.find('.report-board--chart-minutes');
        if (!$button.length) {
            return;
        }
        if (!this.supportsMinutesChartMode()) {
            $button.hide();
            return;
        }
        $button.show();
        const showMinutes = this.view.chartValueMode === 'minutes';
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
        this.syncTableDownloadLink();
    }

    syncTableDownloadLink() {
        syncCapacityTableDownloadHref(
            this.view.$main.find('.report-board--table-download').first(),
            this.view.chartValueMode
        );
    }

    syncDownloadButton() {
        const $button = this.view.$main.find('.report-board--chart-download');
        if (!$button.length) {
            return;
        }

        const hasChart = Boolean(this.view.data && this.view.data.visualization);
        $button.prop('disabled', !hasChart);
        $button.toggleClass('is-disabled', !hasChart);

        const rangeLabel = getChartDateRangeLabel(
            this.view.chartDateFrom,
            this.view.chartDateTo,
            this.view.chartPeriod
        );
        const title = rangeLabel
            ? `Diagramm als Bild herunterladen (${rangeLabel})`
            : 'Diagramm als Bild herunterladen';
        $button.attr('title', title);
        $button.attr('aria-label', title);
    }

    downloadPng() {
        if (!this.view.chart) {
            return;
        }

        const sourceCanvas = this.view.chart.canvas;
        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = sourceCanvas.width;
        exportCanvas.height = sourceCanvas.height;

        const context = exportCanvas.getContext('2d');
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
        context.drawImage(sourceCanvas, 0, 0);

        const link = document.createElement('a');
        link.download = getChartDownloadFilename(
            this.view.chartValueMode,
            this.view.chartDateFrom,
            this.view.chartDateTo,
            this.view.chartPeriod
        );
        link.href = exportCanvas.toDataURL('image/png', 1);
        link.click();
    }

    destroy() {
        if (this.view.chart) {
            this.view.chart.destroy();
            this.view.chart = null;
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
        const supportsMinutes = this.supportsMinutesChartMode();

        if (this.supportsCapacityChannelMode() && this.view.chartChannelMode === 'intern_only') {
            const series = [
                { metric: 'planned' },
                { metric: 'booked' },
            ];

            for (const entry of series) {
                const lineColor = colorlist.shift();
                datasets.push({
                    label: getCapacityTableHeaderLabel(
                        entry.metric,
                        this.view.chartChannelMode,
                        this.view.chartValueMode,
                        supportsMinutes
                    ),
                    data: this.view.data.data.map((row) => getChannelCapacityMetric(
                        row,
                        entry.metric,
                        this.view.chartChannelMode,
                        this.view.chartValueMode,
                        supportsMinutes
                    )),
                    borderColor: lineColor,
                    pointBackgroundColor: lineColor,
                    pointBorderColor: lineColor,
                    fill: false,
                });
            }

            return datasets;
        }

        const activeYLabels = getChartDatasetYLabels(getActiveYLabels(
            this.view.data,
            this.view.chartChannelMode,
            this.view.chartValueMode,
            supportsMinutes
        ));

        for (const datalabel of activeYLabels) {
            const lineColor = colorlist.shift();
            datasets.push({
                label: getLabelInfo(this.view.data.dictionary, datalabel).description,
                data: getListByLabel(this.view.data, datalabel),
                borderColor: lineColor,
                pointBackgroundColor: lineColor,
                pointBorderColor: lineColor,
                fill: false,
            });
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
        const chart = this.view.chart;
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
        const chart = this.view.chart;
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

    updateInPlace() {
        if (!this.view.chart || !this.view.data || !this.view.data.visualization) {
            this.render();
            return;
        }

        const labels = getListByLabel(this.view.data, this.view.data.visualization.xlabel[0]);
        const datasets = this.buildChartDatasets();

        if (!this.hasChartDataChanged(labels, datasets)) {
            return;
        }

        const maxY = this.getMaxYValue(datasets);
        this.patchChartDataInPlace(labels, datasets);

        if (this.view.chart.options.scales?.y) {
            const nextSuggestedMax = maxY === 0 ? 1 : undefined;
            if (this.view.chart.options.scales.y.suggestedMax !== nextSuggestedMax) {
                this.view.chart.options.scales.y.suggestedMax = nextSuggestedMax;
            }
            this.view.chart.options.scales.y.title.text = getYAxisTitle(
                this.view.chartValueMode,
                this.supportsMinutesChartMode()
            );
        }

        if (typeof this.view.chart.stop === 'function') {
            this.view.chart.stop();
        }
        this.view.chart._animationsDisabled = true;
        this.view.chart.update('none');
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
        const visualization = this.view.data.visualization;
        if (!visualization || visualization.labelIntervalHours == null) {
            return null;
        }
        const interval = Number(visualization.labelIntervalHours);
        return Number.isNaN(interval) || interval < 1 ? null : interval;
    }

    usesSparseChartData() {
        return this.shouldHideEmptyChartSlots() && Boolean(this.view.chartDataSparse);
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

    buildChartOptions(labels, maxY) {
        const supportsMinutes = this.supportsMinutesChartMode();
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
                        display: supportsMinutes,
                        text: getYAxisTitle(this.view.chartValueMode, supportsMinutes),
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

    render() {
        if (!this.view.data || !this.view.data.visualization) {
            return;
        }

        this.destroy();

        const labels = getListByLabel(this.view.data, this.view.data.visualization.xlabel[0]);
        const datasets = this.buildChartDatasets();
        const maxY = this.getMaxYValue(datasets);
        this.view.$.find('.chartist').html('<canvas></canvas>&nbsp;');
        this.view.$.find('.chartist').css({
            position: 'relative',
            width: '100%',
            height: '550px'
        });
        const $canvas = this.view.$.find('.chartist canvas');
        const canvascontext = $canvas[0].getContext('2d');
        this.view.chart = new Chart(canvascontext, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: this.buildChartOptions(labels, maxY),
        });
        this.view.chart._animationsDisabled = true;
        this.syncDownloadButton();
        this.syncSparseTimelineButton();
    }
}

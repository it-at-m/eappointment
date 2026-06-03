import BaseView from "../../lib/baseview"
import Chart from "chart.js/auto"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.chart = null;
        this.bindEvents();
        console.log('Component: Warehouse report', this, options);
        this.initChartFromDom();
    }

    bindEvents() {
        this.$main.on('click', '.report-board--refresh', (ev) => {
            ev.preventDefault();
            this.refreshReportContent();
        });
    }

    initChartFromDom() {
        const $chartist = this.$main.find('.chartist').first();
        const chartData = $chartist.attr('data-chartist');
        if (!$chartist.length || !chartData) {
            return;
        }

        $chartist.text('[Initializing chart...]');
        this.data = JSON.parse(chartData);
        $chartist.attr('data-chartist', '');
        this.renderChartjs();
    }

    async refreshReportContent() {
        const $board = this.$main.find('.board').first();
        const $button = this.$main.find('.report-board--refresh');
        if (!$board.length || $button.prop('disabled')) {
            return;
        }

        $button.prop('disabled', true);
        $button.find('i').addClass('fa-spin');

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
            const $newBoard = $parsed.find('.warehouse-report .board').first();

            if (!$newBoard.length) {
                throw new Error('Board markup not found in response');
            }

            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }

            $board.replaceWith($newBoard);
            this.initChartFromDom();
        } catch (error) {
            console.error('Report refresh failed', error);
            window.location.reload();
        } finally {
            this.$main.find('.report-board--refresh').prop('disabled', false)
                .find('i').removeClass('fa-spin');
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
        throw "WarehouseReport: Label " + label + " not found"
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

    shouldShowXTickLabel(label, index, labels) {
        const intervalHours = this.getChartLabelIntervalHours();
        if (intervalHours === null) {
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
        if (labelIntervalHours === null && labels.length <= 31) {
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
            callback(value, index) {
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
        const datasets = [];
        const colorlist = [
            '#008cca',
            '#ffacaa',
            '#d0eaca',
            '#c2c2c2',
            '#efc10f',
        ];
        for (const datalabel of this.data.visualization.ylabel) {
            const dataset = {};
            dataset.label = this.getLabelInfo(datalabel).description;
            dataset.data = this.getListByLabel(datalabel).map((value) => {
                const number = Number(value);
                return Number.isNaN(number) ? 0 : number;
            });
            dataset.borderColor = colorlist.shift();
            dataset.pointBackgroundColor = dataset.borderColor;
            dataset.pointBorderColor = dataset.borderColor;
            dataset.fill = false;
            dataset.spanGaps = false;
            datasets.push(dataset);
        }
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
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                datasets: {
                    line: {
                        clip: false
                    }
                },
                elements: {
                    point: {
                        radius: 3,
                        hoverRadius: 5,
                        hitRadius: 10
                    },
                    line: {
                        tension: 0.25
                    }
                },
                scales: {
                    x: {
                        ticks: this.getXAxisTickOptions(labels)
                    },
                    y: {
                        beginAtZero: true,
                        min: 0,
                        suggestedMax: maxY === 0 ? 1 : undefined,
                        grace: '5%'
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            boxWidth: 12,
                            usePointStyle: false
                        }
                    },
                    tooltip: {
                        mode: 'index'
                    }
                }
            }
        });
    }
}

export default View;

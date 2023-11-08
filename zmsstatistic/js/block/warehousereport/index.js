import BaseView from "../../lib/baseview"
import Chartjs from "chart.js"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.bindEvents();
        console.log('Component: Warehouse report', this, options);
        this.$.find(".chartist").text('[Initializing chart...]');
        this.data = JSON.parse(this.$.find(".chartist").attr('data-chartist'));
        this.$.find(".chartist").attr('data-chartist', '');
        //this.renderChartist(data);
        this.renderChartjs();
    }

    bindEvents() {
        //this.$main.off('click').on('click', '.report-period--show-all', (ev) => {
        //    ev.preventDefault();
        //    ev.stopPropagation();
        //    this.$main.find('.report-period--show-all').hide();
        //    this.$main.find(".report-period--table tr").removeClass('hide');
        //})
    }

    reduceToField(number) {
        return (list, items) => {
            list.push(items[number]);
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
        const list = this.data.data.reduce(this.reduceToField(info.position), []);
        return list;
    }

    renderChartjs() {
        //console.log(this.data);
        //console.log(this.data.visualization.ylabel);
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
            console.log(datalabel);
            dataset.label = this.getLabelInfo(datalabel).description;
            dataset.data = this.getListByLabel(datalabel);
            dataset.borderColor = colorlist.shift();
            dataset.fill = false;
            datasets.push(dataset);
        }
        this.$.find(".chartist").html('<canvas></canvas>&nbsp;');
        this.$.find(".chartist").css({
            "position": "relative",
            "width": "100%",
            "height": "550px"
        });
        const $canvas = this.$.find(".chartist canvas");
        const canvascontext = $canvas[0].getContext('2d');
        //console.log(datasets);
        new Chartjs(canvascontext, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                elements: {
                    line: {
                        tension: 0.25 // bezier error
                    }
                },
                legend: {
                    display: true,
                    labels: {
                        boxWidth: 12,
                        usePointStyle: false
                    }
                },
                tooltips: {
                    mode: "index"
                }
            }
        });
    }
}

export default View;

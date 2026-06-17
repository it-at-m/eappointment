// --------------------------------------------------------
// ZMS Statistic behavior
// --------------------------------------------------------

// Import base libs
//import window from "window";
import $ from "jquery";
import moment from 'moment'
import 'moment/locale/de';
//import bindReact from './lib/bindReact.js'
import { getDataAttributes } from './lib/utils'
import PeriodListView from './block/periodlist'
import WarehouseReportView from './block/warehousereport'
import CapacityReportView from './block/capacityreport'
import ReportFilterView from './block/reportfilter'

// Bind jQuery on $ for testing
window.$ = $;

moment.locale('de')

// Say hello
console.log("Welcome to the ZMS statistics interface...");

$('.report-index').each(function () {
    new PeriodListView(this, getDataAttributes(this));
})

$('.warehouse-report').each(function () {
    new WarehouseReportView(this, getDataAttributes(this));
})

$('.capacity-report').each(function () {
    new CapacityReportView(this, getDataAttributes(this));
})

$('[data-report-filter]').each(function () {
    new ReportFilterView(this, getDataAttributes(this));
})

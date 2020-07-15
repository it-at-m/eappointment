// --------------------------------------------------------
// ZMS Statistic behavior
// --------------------------------------------------------

// Import base libs
//import window from "window";
import $ from "jquery";
import moment from 'moment'
import 'moment/locale/de';
//import bindReact from './lib/bindReact.js'
import { getDataAttributes, forceHttps } from './lib/utils'
import PeriodListView from './block/periodlist'
import WarehouseReportView from './block/warehousereport'

// Bind jQuery on $ for testing
window.$ = $;

moment.locale('de')

// Force https protocol
forceHttps();

// Say hello
console.log("Welcome to the ZMS statistics interface...");

$('.report-index').each(function () {
    new PeriodListView(this, getDataAttributes(this));
})

$('.warehouse-report').each(function () {
    new WarehouseReportView(this, getDataAttributes(this));
})

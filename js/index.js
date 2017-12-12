// --------------------------------------------------------
// ZMS Statistic behavior
// --------------------------------------------------------

import 'babel-polyfill';

// Import base libs
import window from "window";
import $ from "jquery";
import moment from 'moment'
import 'moment/locale/de';
//import bindReact from './lib/bindReact.js'
import { getDataAttributes } from './lib/utils'

import PeriodListView from './block/periodlist'

// Bind jQuery on $ for testing
window.$ = $;

moment.locale('de')

// Say hello
console.log("Welcome to the ZMS statistic interface...");

$('.report-period').each(function() {
    new PeriodListView(this, getDataAttributes(this));
})

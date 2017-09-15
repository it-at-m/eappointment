// --------------------------------------------------------
// ZMS Statistic behavior
// --------------------------------------------------------

import 'babel-polyfill';

// Import base libs
import window from "window";
import $ from "jquery";
import moment from 'moment'
import 'moment/locale/de';

// Bind jQuery on $ for testing
window.$ = $;

moment.locale('de')

// Say hello
console.log("Welcome to the ZMS statistic interface...");

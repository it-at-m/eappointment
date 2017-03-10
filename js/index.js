// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------

import 'babel-polyfill';

// Import base libs
import window from "window";
import $ from "jquery";
import moment from 'moment'
import 'moment/locale/de';

// Import Views
import FormView from "./element/form";
import PickupKeyboardHandheldView from "./block/pickup-keyboard-handheld";
import EmergencyView from './block/emergency'
import DepartmentLinksView from './block/department/links'
import DepartmentDaysOffView from './block/department/daysOff'
import TicketPrinterConfigView from './block/ticketprinter/config'
import CallDisplayConfigView from './block/calldisplay/config'
import CounterView from './page/counter'

import AvailabilityDayPage from './page/availabilityDay'
import bindReact from './lib/bindReact.js'
import { getDataAttributes } from './lib/utils'

import preventFormResubmit from './element/form/preventFormResubmit'



// Bind jQuery on $ for testing
window.$ = $;

moment.locale('de')

// Init Views
$('form').each(function() { new FormView(this);});
$('.pickup-keyboard-handheld').each(function() { new PickupKeyboardHandheldView(this);});
$('.emergency').each(function() {
    new EmergencyView(this, getDataAttributes(this));
})

$('.department-links').each(function() {
    new DepartmentLinksView(this, getDataAttributes(this));
})

$('.counter-view').each(function() {
    new CounterView(this, getDataAttributes(this));
})

$('form').each(function() {
    preventFormResubmit(this);
})

// Say hello
console.log("Welcome to the ZMS admin interface...");


// hook up react components
bindReact('.availabilityDayRoot', AvailabilityDayPage)
bindReact('[data-department-daysoff]', DepartmentDaysOffView)
bindReact('[data-ticketprinter-config]', TicketPrinterConfigView)
bindReact('[data-calldisplay-config]', CallDisplayConfigView)

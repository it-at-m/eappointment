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
import EmergencyView from './block/emergency'
//import DepartmentLinksView from './block/department/links'
//import DepartmentDaysOffView from './block/department/daysOff'
//import TicketPrinterConfigView from './block/ticketprinter/config'
//import CallDisplayConfigView from './block/calldisplay/config'
import CounterView from './page/counter'
import WorkstationView from './page/workstation'
import PickupView from './page/pickup'
import PickupHandheldView from './page/pickup/handheld'
import PickupKeyboardHandheldView from "./page/pickup/keyboard-handheld"
import StatisticView from './page/statistic'

import ScopeSelectView from './block/scopeselectform'
//import AvailabilityDayPage from './page/availabilityDay'
import WeekCalendarPage from './page/weekCalendar'
import printScopeAppointmentsByDay from './page/scopeAppointmentsByDay/print'
import printWaitingNumber from './page/waitingnumber/print'
//import bindReact from './lib/bindReact.js'
import { getDataAttributes } from './lib/utils'
import { forceHttps } from './lib/utils'

import scopeChangeProvider from './element/form/scope'
import preventFormResubmit from './element/form/preventFormResubmit'
import maxChars from './element/form/maxChars'
import DialogHandler from './lib/dialogHandler'


// Bind jQuery on $ for testing
window.$ = $;

moment.locale('de')

// Force https protocol
forceHttps();

// Init Views
$('.emergency').each(function() {
    new EmergencyView(this, getDataAttributes(this));
})

$('.metalinks').each(function() {
    //new MetaLinksView(this, getDataAttributes(this));
})

$('.counter-view').each(function() {
    new CounterView(this, getDataAttributes(this));
})

$('.workstation-view').each(function() {
    new WorkstationView(this, getDataAttributes(this));
})

$('.calendar-weektable').each(function() {
    new WeekCalendarPage(this, getDataAttributes(this));
})

$('[data-scope-select-form]').each(function() {
    new ScopeSelectView(this, getDataAttributes(this));
})

$('.pickup-view').each(function() {
    new PickupView(this, getDataAttributes(this));
})

$('.pickup-handheld-view').each(function() {
    new PickupHandheldView(this, getDataAttributes(this));
})

$('.pickup-keyboard-handheld').each(function() {
    new PickupKeyboardHandheldView(this);
});

$('.client-processed').each(function() {
    new StatisticView(this, getDataAttributes(this));
})

$('form').each(function() {
    preventFormResubmit(this);
})

$('textarea.maxchars').each(function() {
    maxChars(this);
})

$('.scope-form-update').each(function() {
    scopeChangeProvider(this);
});

printScopeAppointmentsByDay();
printWaitingNumber();
DialogHandler.hideMessages();

// Say hello
console.log("Welcome to the ZMS admin interface...");


// hook up react components
//bindReact('.availabilityDayRoot', AvailabilityDayPage)
//bindReact('[data-department-daysoff]', DepartmentDaysOffView)
//bindReact('[data-ticketprinter-config]', TicketPrinterConfigView)
//bindReact('[data-calldisplay-config]', CallDisplayConfigView)

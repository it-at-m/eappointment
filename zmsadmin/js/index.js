// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------

//import 'babel-polyfill';

// Import base libs
import $ from "jquery";
import moment from 'moment/min/moment-with-locales';

// Import Views
//import AccessKeyView from './page/accesskeys'
import EmergencyView from './block/emergency'
//import LoginFormView from './block/index/loginform'
//import DepartmentLinksView from './block/department/links'
//import DepartmentDaysOffView from './block/department/daysOff'
//import TicketPrinterConfigView from './block/ticketprinter/config'
//import CallDisplayConfigView from './block/calldisplay/config'
import ConfigView from './page/config'
import CounterView from './page/counter'
import WorkstationView from './page/workstation'
import UseraccountView from './page/useraccount'
import PickupView from './page/pickup'
import PickupHandheldView from './page/pickup/handheld'
import PickupKeyboardHandheldView from "./page/pickup/keyboard-handheld"
import StatisticView from './page/statistic'

import LoginScopeSelectView from './block/scope/loginselectform'
import EmergencyEnd from './block/scope/emergencyend'
//import AvailabilityDayPage from './page/availabilityDay'
import WeekCalendarPage from './page/weekCalendar'
import printScopeAppointmentsByDay from './page/scopeAppointmentsByDay/print'
import printWaitingNumber from './page/waitingnumber/print'
//import bindReact from './lib/bindReact.js'
import { getDataAttributes, forceHttps } from './lib/utils'

import preventFormResubmit from './element/form/preventFormResubmit'
import focusFirstErrorElement from './element/form/focusFirstErrorElement'
import maxChars from './element/form/maxChars'
import DialogHandler from './lib/dialogHandler'

// Import JS from patternlab
import accordion from 'bo-layout-admin-js/behavior/accordion';
import tabs from 'bo-layout-admin-js/behavior/tabs';
import collapse from 'bo-layout-admin-js/behavior/collapse';
import stickytrigger from 'bo-layout-admin-js/behavior/form-actions--sticky-trigger';
import navigationprimary from 'bo-layout-admin-js/behavior/navigation-primary';
import datepicker from 'bo-layout-admin-js/behavior/datepicker';
import infotext from 'bo-layout-admin-js/behavior/form-infotext-flyin';
import metanavi from 'bo-layout-admin-js/behavior/header-metanavi-popup';
import formalerts from 'bo-layout-admin-js/behavior/formalerts-flyin';

// load patternlab JS
function loadResources() {
    accordion();
    tabs();
    collapse();
    datepicker();
    stickytrigger();
    infotext();
    formalerts();
    metanavi();
    navigationprimary();
}

// Bind jQuery on $ for testing
window.$ = $;

moment.locale('de')

// Force https protocol
forceHttps();

// Init Views
//new AccessKeyView(document);

loadResources();

$('.emergency').each(function () {
    new EmergencyView(this, getDataAttributes(this));
})

$('.metalinks').each(function () {
    //new MetaLinksView(this, getDataAttributes(this));
})

$('.counter-view').each(function () {
    new CounterView(this, getDataAttributes(this));
})

$('.workstation-view').each(function () {
    new WorkstationView(this, getDataAttributes(this));
})

$('.useraccount-edit-view').each(function () {
    new UseraccountView(this, getDataAttributes(this));
})

$('.calendar-weektable').each(function () {
    new WeekCalendarPage(this, getDataAttributes(this));
})

$('.config-view').each(function () {
    new ConfigView(this, getDataAttributes(this));
})


$('[data-scope-select-form]').each(function () {
    new LoginScopeSelectView(this, getDataAttributes(this));
})

$('.emergency-end').each(function () {
    new EmergencyEnd(this, getDataAttributes(this));
})

$('.pickup-view').each(function () {
    new PickupView(this, getDataAttributes(this));
})

$('.pickup-handheld-view').each(function () {
    new PickupHandheldView(this, getDataAttributes(this));
})

$('.pickup-keyboard-handheld').each(function () {
    new PickupKeyboardHandheldView(this);
});

$('.client-processed').each(function () {
    new StatisticView(this, getDataAttributes(this));
})

$('form').each(function () {
    preventFormResubmit(this);
    focusFirstErrorElement(this);
})

$('textarea.maxchars').each(function () {
    maxChars(this);
})

printScopeAppointmentsByDay();
printWaitingNumber();
DialogHandler.hideMessages();

// Say hello
console.log("Welcome to the ZMS admin interface...");
console.log("Hello")


// hook up react components
//bindReact('.availabilityDayRoot', AvailabilityDayPage)
//bindReact('[data-department-daysoff]', DepartmentDaysOffView)
//bindReact('[data-ticketprinter-config]', TicketPrinterConfigView)
//bindReact('[data-calldisplay-config]', CallDisplayConfigView)



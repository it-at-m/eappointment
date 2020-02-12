// --------------------------------------------------------
// ZMS Ticketprinter behavior
// --------------------------------------------------------

// Import base libs
import $ from "jquery";
import settings from './settings';
//import { forceHttps } from './lib/utils'

// Import Views
import Reload from "./page/main";
import GetHash from "./page/newhash";
import PrintDialog from "./page/process";
import DigitalTime from "./block/digital-clock";
import NotificationKeyboardHandheldView from "./block/notification-keyboard-handheld";
import preventFormResubmit from './element/form/preventFormResubmit'

// Bind jQuery on $ for testing
window.$ = $;
window.bo = {
    "zmsticketprinter": settings
};

// Init Views
$('#newhash').each(function() { new GetHash(this);});
$('#index, #message, #exception').each(function() { new Reload(this);});
$('#process').each(function() { new PrintDialog(this);});
$('.digitaluhr').each(function() { new DigitalTime(this);});
$('.smsbox').each(function() { new NotificationKeyboardHandheldView(this);});

// prevent resubmits
$('form').each(function() {
    preventFormResubmit(this);
})

// Say hello
console.log("Welcome to the ZMS Ticketprinter interface...");

// Force https protocol
//forceHttps();

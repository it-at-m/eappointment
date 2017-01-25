// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------

// Import base libs
import window from "window";
import $ from "jquery";
import settings from './settings';

window.bo = {
    "zmsticketprinter": settings
};

// Import Views
import Reload from "./page/main";
import PrintDialog from "./page/process";
import DigitalTime from "./block/digital-clock";
import NotificationKeyboardHandheldView from "./block/notification-keyboard-handheld";

// Init Views
$('#index, #message, #exception').each(function() { new Reload(this);});
$('#process').each(function() { new PrintDialog(this);});
$('.digitaluhr').each(function() { new DigitalTime(this);});
$('.smsbox').each(function() { new NotificationKeyboardHandheldView(this);});

// Say hello
console.log("Welcome to the ZMS Ticketprinter interface...");

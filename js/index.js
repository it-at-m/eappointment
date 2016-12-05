// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------

// Import base libs
import window from "window";
import $ from "jquery";
import settings from './settings';

// Import Views
import Reload from "./page/main";
import DigitalTime from "./block/digital-clock";
import NotificationKeyboardHandheldView from "./block/notification-keyboard-handheld";

window.bo = {
    "zmsticketprinter": settings
};

// Init Views
new Reload(this);
$('.digitaluhr').each(function() { new DigitalTime(this);});
$('.smsbox').each(function() { new NotificationKeyboardHandheldView(this);});

// Say hello
console.log("Welcome to the ZMS Ticketprinter interface..."); 

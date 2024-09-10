// --------------------------------------------------------
// ZMS Ticketprinter behavior
// --------------------------------------------------------

// Import base libs
import $ from "jquery";
import settings from './settings';
import Reload from "./page/main";
import GetHash from "./page/newhash";
import PrintDialog from "./page/process";
import DigitalTime from "./block/digital-clock";
import NotificationKeyboardHandheldView from "./block/notification-keyboard-handheld";
import preventFormResubmit from './element/form/preventFormResubmit'

window.$ = $;
window.bo = {
    "zmsticketprinter": settings
}; 

$('#newhash').each(function() { new GetHash(this);});
$('#index, #message, #exception').each(function() { new Reload(this);});
$('#process').each(function() { new PrintDialog(this);});
if ($('.digitaluhr').length > 0) {
    console.log('Found digitaluhr elements, initializing DigitalTime...');
    $('.digitaluhr').each(function() { new DigitalTime(this); });
} else {
    console.log('No digitaluhr elements found.');
}
$('.smsbox').each(function() { new NotificationKeyboardHandheldView(this);});

$('form').each(function() {
    preventFormResubmit(this);
})

console.log("Welcome to the ZMS Ticketprinter interface...");


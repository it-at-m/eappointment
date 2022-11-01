// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------

// Import base libs
import $ from "jquery";
import settings from './settings';

window.bo = {
    "zmscalldisplay": settings
};

// Import Views
import AnalogClock from "./block/analogClock";
import QueueList from "./block/queueList";
import WaitingInfo from "./block/waitingInfo";
import QrCode from "./block/qrCode";

// Init Views
$('#Uhr').each(function() { new AnalogClock();});
$('#queueImport').each(function() { 
    new QueueList();
    new WaitingInfo();
});
new QrCode();

// Say hello
console.log("Welcome to the ZMS Calldisplay interface...");

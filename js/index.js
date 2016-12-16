// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------

// Import base libs
import window from "window";
import $ from "jquery";
import settings from './settings';

window.bo = {
    "zmscalldisplay": settings
};

// Import Views
import AnalogClock from "./block/analogClock";
import QueueList from "./block/queueList";

// Init Views
$('#Uhr').each(function() { new AnalogClock(this);});
$('#queueImport').each(function() { new QueueList(this);});

// Say hello
console.log("Welcome to the ZMS Calldisplay interface..."); 

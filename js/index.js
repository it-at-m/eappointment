// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------

// Import base libs
import window from "window";
import $ from "jquery";

// Import Views
import DigitalTime from "./block/digital-clock";
import PickupKeyboardHandheldView from "./block/pickup-keyboard-handheld";

// Bind jQuery on $ for testing
window.$ = $;

// Init Views
$('.digitaluhr').each(function() { new DigitalTime(this);});
$('.pickup-keyboard-handheld').each(function() { new PickupKeyboardHandheldView(this);});

// Say hello
console.log("Welcome to the ZMS admin interface..."); 

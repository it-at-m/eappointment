// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------

// Import base libs
import window from "window";
import $ from "jquery";

// Import Views
import FormView from "./element/form";
import AvailabilityTimetableView from "./block/availability-timetable";
import AvailabilityFormView from "./block/availability-form";
import PickupKeyboardHandheldView from "./block/pickup-keyboard-handheld";

// Bind jQuery on $ for testing
window.$ = $;

// Init Views
$('form').each(function() { new FormView(this);});
$('.availability-timetable').each(function() { new AvailabilityTimetableView(this);});
$('.availability-form').each(function() { new AvailabilityFormView(this);});
$('.pickup-keyboard-handheld').each(function() { new PickupKeyboardHandheldView(this);});

// Say hello
console.log("Welcome to the ZMS admin interface...");

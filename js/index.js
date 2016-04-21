// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------

// Import base libs
import window from "window";
import $ from "jquery";

// Import Views
import FormView from "./element/form";
import AvailabilityTimetableView from "./element/availability-timetable";

// Bind jQuery on $ for testing
window.$ = $;

// Init Views
$('form').each(function() { new FormView(this);});
$('.availability-timetable').each(function() { new AvailabilityTimetableView(this);});

// Say hello
console.log("Welcome to the ZMS admin interface...");

// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------

// Import base libs
import window from "window";
import $ from "jquery";
import welcomeText from "./template/welcome.html";
import settings from './settings';

// Import Views
//import DayselectView from "./page/dayselectView";

// Bind jQuery on $ for testing
window.$ = $;
window.bo = {
    "zmsticketprinter": settings,
    "test": {
    },
};
// Init Views
//$('#dayselect').each(() => new DayselectView(this));

// Say hello
console.log(welcomeText);

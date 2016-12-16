
import BaseView from '../lib/baseview';
import $ from "jquery";
import settings from '../settings';
import CoolClock from '../lib/coolclock.js';

class View extends BaseView {

    constructor (element) {	
        super(element);  
        this.bindPublicMethods('initClock');
        console.log("Found analog clock");
        this.$.ready(this.initClock);
    }    
    
    initClock () {	
	window.CoolClock.config.skins = {
            themed: {
                outerBorder: { lineWidth: 1, radius:95, color: "black", alpha: 0 },
                smallIndicator: { lineWidth: 1, startAt: 89, endAt: 93, color: "#4C4C4C", alpha: 1 },
                largeIndicator: { lineWidth: 5, startAt: 85, endAt: 93, color: "#4C4C4C", alpha: 1 },
                hourHand: { lineWidth: 7, startAt: -10, endAt: 50, color: "#4C4C4C", alpha: 1 },
                minuteHand: { lineWidth: 5, startAt: -10, endAt: 75, color: "#4C4C4C", alpha: 1 },
                secondHand: { lineWidth: 1, startAt: -10, endAt: 85, color: "#4C4C4C", alpha: 1 },
                secondDecoration: { lineWidth: 1, startAt: 70, radius: 4, fillColor: "red", color: "red", alpha: 0 }
            }
        };
	window.CoolClock.findAndCreateClocks();
    }
}

export default View;

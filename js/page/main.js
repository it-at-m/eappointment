import BaseView from '../lib/baseview';
import settings from '../settings';
import window from "window";
import $ from "jquery";
import cookie from "js-cookie";

class View extends BaseView {

    constructor (element) {	
        super(element);        
        this.bindPublicMethods('setInterval');
        console.log('Redirect to home url every 30 seconds');
        this.$.ready(this.setInterval);
    }
    
    reload () {	
	if (cookie.get("Ticketprinter_Homeurl")) {
	    window.location.href = cookie.get("Ticketprinter_Homeurl");
	} else {
	    window.location.reload();
	}	
    }
    
    setInterval () {	
	setInterval(this.reload, 30000); 
    }
}

export default View;

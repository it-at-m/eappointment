import BaseView from '../lib/baseview';
import settings from '../settings';
import window from "window";
import $ from "jquery";

class View extends BaseView {

    constructor (element) {	
        super(element);        
        this.bindPublicMethods('printDialog', 'preventRefresh', 'reload');
        console.log('Print data and redirect to home url after presetted time');
        this.preventRefresh();
        this.$.ready(this.printDialog);
    }
    
    reload () {		
    	window.location.href = this.getUrl('/home/');	
    }
    
    getUrl (relativePath) {
        let includepath = window.bo.zmsticketprinter.includepath;
        return includepath + relativePath;
    }
    
    printDialog () {
    	document.title = "Anmeldung an Warteschlange";
    	window.print();
    	let reloadTime = window.bo.zmsticketprinter.reload;
    	setTimeout(this.reload, reloadTime * 1000); // default is 30
    }
    
    preventRefresh() {
    	document.onkeydown = function(event) {
    		 if(window.event){
    		       event.preventDefault();
    		 }
    	};
    }
}

export default View;

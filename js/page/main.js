import BaseView from '../lib/baseview';
import settings from '../settings';
import window from "window";
import $ from "jquery";

class View extends BaseView {

    constructor (element) {	
        super(element);        
        this.bindPublicMethods('setInterval', 'reload');
        console.log('Redirect to home url every 30 seconds');
        this.$.ready(this.setInterval);
    }
    
    reload () {		
	window.location.href = this.getUrl('/home/');	
    }
    
    setInterval () {
	setInterval(this.reload, 30000);
    }
    
    getUrl (relativePath) {
        let includepath = window.bo.zmsticketprinter.includepath;
        return includepath + relativePath;
    }
}

export default View;

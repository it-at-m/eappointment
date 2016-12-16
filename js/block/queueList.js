
import BaseView from '../lib/baseview';
import $ from "jquery";

class View extends BaseView {

    constructor (element) {	
        super(element);  
        this.bindPublicMethods('initRequest', 'setInterval');
        console.log("Found queueList container");
        this.$.ready(this.initRequest);
        $.ajaxSetup({ cache: false });
    }    
    
    initRequest () {
	$.post( this.getUrl('/queue/'), window.bo.zmscalldisplay)
		.done(data => {
        	    $( "#queueImport" ).html( data );        	    
        	    this.setWaitingClients(data);
        	    this.setWaitingTime(data);
        	    this.setInterval();
	});
	
	this.startRingAudio();
	this.stopRingAudio();
    }   
    
    setInterval () {
    	var reloadTime = window.bo.zmscalldisplay.reloadInterval;  
    	setTimeout(this.initRequest, reloadTime * 1000);
    }
    
    getUrl (relativePath) {
        let includepath = window.bo.zmscalldisplay.includepath;
        let queryparams = window.bo.zmscalldisplay.queryparams;
        return includepath + relativePath;
    }
    
    setWaitingClients (data)
    {
	var waitingClients = $(data).filter("div#waitingClients").text();
	if (0 < waitingClients) {
	    $("#wartende").html(waitingClients);
	}
    }
    
    setWaitingTime (data)
    {
	var waitingTime = $(data).filter("div#waitingTime").text();
	if (0 < waitingTime) {
	    if (120 < waitingTime) {
		$("#wartezeit").html(Math.floor(waitingTime/60) + " Stunden");
	    } else {
		$("#wartezeit").html(waitingTime + " Minuten");
	    }
	    
	}
    }
    
    startRingAudio()
    {
	$( "#ring" ).trigger('play');
    }
    
    stopRingAudio(){
	setTimeout(function(){
	    $("#ring").trigger('pause');
	    //set play time to 0
	    $("#ring").prop("currentTime",0);
	},4000);	  
    }
}

export default View;

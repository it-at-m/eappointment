
import BaseView from '../lib/baseview';
import $ from "jquery";
import RingAudio from "../block/ringAudio";

class View extends BaseView {

    constructor (element) {	
        super(element);  
        this.bindPublicMethods('initRequest', 'setInterval');
        console.log('Found queueList container');
        this.$.ready(this.initRequest);        
        $.ajaxSetup({ cache: false });
    }    
    
    initRequest () {
	$.post( this.getUrl('/queue/'), window.bo.zmscalldisplay)
	    .done(data => {
        	$( '#queueImport' ).html( data );
        	this.setColorForNewCall();
        	this.setWaitingClients(data);
        	this.setWaitingTime(data);
        	this.setInterval();
        	var audioCheck = new RingAudio();
        	audioCheck.initSoundCheck();
        	this.getDestinationToNumber();
	    });
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
    
    setColorForNewCall()
    {
    	let isNewTime = window.bo.zmscalldisplay.serverTime;    	
    	$( '#queueImport td.wartenummer[data-callTime]').each(function() {
    		if (parseInt($(this).attr('data-callTime')) + window.bo.zmscalldisplay.queue.timeUntilOld > isNewTime) {    			
    			$("div.aufrufanzeigenummer", this).addClass('newprocess');
    		}
    	});
    }
    
    getDestinationToNumber()
    {
	if (window.bo.zmscalldisplay.queue.showOnlyNumeric) {	    
	    $( '#queueImport .destination').each(function() {		
		    let string = $(this).text();
		    let regex = /\d/g;
		    if (regex.test(string)) {
			$(this).text(string.replace(/\D/g,''));
		    }
		    
		});
	}	
    }
}

export default View;

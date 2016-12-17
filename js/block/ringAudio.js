
import BaseView from '../lib/baseview';
import $ from "jquery";

class View extends BaseView {
    
	constructor (element) {	
        super(element);  
        this.bindPublicMethods('initSoundCheck');
    }   
	
	initSoundCheck ()
    {
    	if (this.hasNewQueueId()) {
    		this.playSound();    		
    	}
    	let newQueueIds = this.getCalledQueueIds();    		
    	this.writeCalledQueueIds(newQueueIds);
    }
    
    hasNewQueueId()
    {
    	let newQueueIds = this.getCalledQueueIds();
    	let oldQueueIds = window.bo.zmscalldisplay.queue.calledIds;    
    	let diff = $(newQueueIds).not(oldQueueIds).get();
    	return (0 < diff.length);
    }
    
    getCalledQueueIds()
    {
    	let queueIds = [];
    	$( '#queueImport td.wartenummer span[data-appointment]').each(function() {
    		queueIds.push($(this).attr('data-appointment'));  		  	
    	});
    	queueIds.sort((a,b) => {return a - b;}).join(',');
    	return queueIds;
    }

    playSound()
    {
    	$( "#ring" ).trigger('play');
		setTimeout(function(){
		    $("#ring").trigger('pause');
		    //set play time to 0
		    $("#ring").prop("currentTime",0);
		},4000);
    }
    
    writeCalledQueueIds (newQueueIds)
    {      	
    	window.bo.zmscalldisplay.queue.calledIds = newQueueIds;
    }
}

export default View;

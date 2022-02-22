import BaseView from '../lib/baseview';
import $ from "jquery";

class View extends BaseView {

    constructor(element) {
        super(element);
        this.bindPublicMethods('initSoundCheck');
    }


    initSoundCheck() {
        let audioPlay = $("#ring");
        if (this.hasNewQueueId()) {
            audioPlay.get(0).play();
        }
        let newQueueIds = this.getCalledQueueIds();
        this.writeCalledQueueIds(newQueueIds);
    }

    hasNewQueueId() {
        let newQueueIds = this.getCalledQueueIds();
        let oldQueueIds = window.bo.zmscalldisplay.queue.calledIds;
        let diff = $(newQueueIds).not(oldQueueIds).get();
        return (0 < diff.length);
    }

    getCalledQueueIds() {
        let queueIds = [];
        $('#queueImport td.wartenummer span[data-status]').each(function () {
            if ('called' == $(this).attr('data-status') || 'pickup' == $(this).attr('data-status')) {
                queueIds.push($(this).attr('data-appointment'));
            }
            
        });
        queueIds.sort((a, b) => { return a - b; }).join(',');
        return queueIds;
    }

    writeCalledQueueIds(newQueueIds) {
        window.bo.zmscalldisplay.queue.calledIds = newQueueIds;
    }
}

export default View;

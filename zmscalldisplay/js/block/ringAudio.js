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

    getNewQueueIds() {
        const newQueueIds = this.getCalledQueueIds();
        const rawOld = window.bo.zmscalldisplay.queue.calledIds;
        const oldQueueIds = Array.isArray(rawOld)
            ? rawOld
            : (rawOld != null && rawOld !== '' ? [rawOld] : []);
        const oldSet = new Set(oldQueueIds.map(id => String(id)));
        return newQueueIds.filter(id => !oldSet.has(String(id)));
    }

    hasNewQueueId() {
        return (0 < this.getNewQueueIds().length);
    }

    getCalledQueueIds() {
        let queueIds = [];
        $('#queueImport td.wartenummer span[data-status]').each(function () {
            if ('called' == $(this).attr('data-status')) {
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

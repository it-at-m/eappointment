import BaseView from '../lib/baseview';
import $ from "jquery";
import RingAudio from "../block/ringAudio";

class View extends BaseView {

    constructor(element) {
        super(element);
        this.bindPublicMethods('initRequest', 'setInterval');
        console.log('Found queueList container');
        this.$.ready(this.initRequest);
        $.ajaxSetup({ cache: false });
    }

    initRequest() {
        const ajaxopt = {
            type: "POST",
            url: this.getUrl('/queue/'),
            data: window.bo.zmscalldisplay,
            timeout: ((window.bo.zmscalldisplay.reloadInterval * 1000) - 100)
        };
        $.ajax(ajaxopt)
            .done(data => {
                this.hideMessages(0);
                $('#queueImport').html(data);
                //this.setColorForNewCall();
                this.setWaitingClients(data);
                this.setWaitingTime(data);
                var audioCheck = new RingAudio();
                audioCheck.initSoundCheck();
                this.getDestinationToNumber();
            })
            .fail(function () {
                $('.fatal').show();
            });
        this.setInterval();
    }

    setInterval() {
        var reloadTime = window.bo.zmscalldisplay.reloadInterval;
        setTimeout(this.initRequest, reloadTime * 1000);
    }

    getUrl(relativePath) {
        let includepath = window.bo.zmscalldisplay.includepath;
        return includepath + relativePath;
    }

    setWaitingClients(data) {
        var waitingClients = $(data).filter("div#waitingClients").text();
        if (0 < waitingClients) {
            $("#wartende").html(waitingClients);
        }
    }

    setWaitingTime(data) {
        var waitingTime = $(data).filter("div#waitingTime").text();
        $("#wartezeit").html(waitingTime);
    }

    /*
    setColorForNewCall() {
        let isNewTime = window.bo.zmscalldisplay.serverTime;
        $('#queueImport td.wartenummer[data-callTime]').each(function () {
            if (parseInt($(this).attr('data-callTime')) + window.bo.zmscalldisplay.queue.timeUntilOld > isNewTime) {
                $("div.aufrufanzeigenummer", this).addClass('newprocess');
            }
        });
    }
    */

    getDestinationToNumber() {
        if (window.bo.zmscalldisplay.queue.showOnlyNumeric) {
            $('#queueImport .destination').each(function () {
                let string = $(this).text();
                let regex = /\d/g;
                if (regex.test(string)) {
                    $(this).text(string.replace(/\D/g, ''));
                }
            });
        }
    }

    hideMessages(delay = 5000) {
        let message = $.find('[data-hide-message]');
        if (message.length) {
            setTimeout(() => {
                $(message).fadeOut();
            }, delay)
        }
    }
}

export default View;

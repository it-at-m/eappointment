import BaseView from '../lib/baseview';
import $ from "jquery";
import AnalogClock from "./analogClock";

class View extends BaseView {

    constructor(element) {
        super(element);
        this.bindPublicMethods('load', 'setInterval');
        console.log('Found waitingInfo container');
        $(window).on(
            'load', () => {
                this.load();
            }
        );
        $.ajaxSetup({ cache: false });
    }

    load() {
        new AnalogClock();
        const ajaxopt = {
            type: "POST",
            url: this.getUrl('/waitinginfo/'),
            data: window.bo.zmscalldisplay,
            timeout: ((window.bo.zmscalldisplay.queue.timeWaitingInfo * 1000) - 100)
        };
        $.ajax(ajaxopt)
            .done(data => {
                this.hideMessages(0);
                this.setWaitingClients(data);
                this.setWaitingTime(data);
            })
            .fail(function () {
                $('.fatal').show();
            });
        this.setInterval();
    }

    setInterval() {
        var reloadTime = window.bo.zmscalldisplay.queue.timeWaitingInfo;
        setTimeout(this.load, reloadTime * 1000);
    }

    getUrl(relativePath) {
        let includepath = window.bo.zmscalldisplay.includepath;
        return includepath + relativePath;
    }

    setWaitingClients(data) {
        var waitingClients = $(data).filter("div#waitingClients").text();
        if (0 <= waitingClients) {
            $("#wartende").html(waitingClients);
        }
    }

    setWaitingTime(data) {
        var waitingTime = $(data).filter("div#waitingTime").text();
        $("#wartezeit").html(waitingTime);
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

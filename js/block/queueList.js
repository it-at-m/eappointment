import BaseView from '../lib/baseview';
import $ from "jquery";
import RingAudio from "./ringAudio";

class View extends BaseView {

    constructor(element) {
        super(element);
        this.bindPublicMethods('initRequest', 'setInterval');
        console.log('Found queueList container');
        $(window).on(
            'load', () => {
                this.initRequest();
            }
        );
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

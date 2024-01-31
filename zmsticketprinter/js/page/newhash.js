import BaseView from '../lib/baseview';
import cookie from "js-cookie";
import $ from "jquery";

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods('load','setTimeout','getUrl','reloadPage');
        $(window).on('load', this.load);
    }

    load () {
        cookie.remove("Ticketprinter");
        this.setTimeout();
    }

    reloadPage () {
        window.location.href = this.getUrl('/home/');
    }

    setTimeout () {
        setTimeout(this.reloadPage, 5000);
    }

    getUrl (relativePath) {
        let includepath = window.bo.zmsticketprinter.includepath;
        return includepath + relativePath;
    }
}

export default View;

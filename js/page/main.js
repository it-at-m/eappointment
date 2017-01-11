/* global setInterval */
import BaseView from '../lib/baseview';
import window from "window";

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods('setInterval', 'reloadPage');
        console.log('Redirect to home url every 30 seconds');
        this.$.ready(this.setInterval);
    }

    reloadPage () {
        window.location.href = this.getUrl('/home/');
    }

    setInterval () {
        var reloadTime = window.bo.zmsticketprinter.reloadInterval;
        setInterval(this.reloadPage, reloadTime * 1000);
    }

    getUrl (relativePath) {
        let includepath = window.bo.zmsticketprinter.includepath;
        return includepath + relativePath;
    }
}

export default View;

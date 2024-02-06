import BaseView from '../lib/baseview';
import $ from "jquery";

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods('setInterval', 'reloadPage');
        console.log('Redirect to home url every 30 seconds');
        $(function() {this.setInterval});
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

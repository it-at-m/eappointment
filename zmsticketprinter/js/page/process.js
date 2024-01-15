import BaseView from '../lib/baseview';
import $ from "jquery";

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods('printDialog', 'reload');
        console.log('Print data and redirect to home url after presetted time');
        $(function() {this.printDialog});
    }

    reload () {
        window.location.href = this.getUrl('/home/');
    }

    getUrl (relativePath) {
        let includepath = window.bo.zmsticketprinter.includepath;
        return includepath + relativePath;
    }

    printDialog () {
        document.title = "Anmeldung an Warteschlange";
        window.print();

        var beforePrint = () => {
            console.log('start printing');
        };
        var afterPrint = () => {
            let reloadTime = window.bo.zmsticketprinter.reloadInterval;
            setTimeout(() => {
                this.reload();
            }, reloadTime * 1000); // default is 30
        };

        if (window.matchMedia) {
            var mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener(function(mql) {
                if (mql.matches) {
                    beforePrint();
                } else {
                    afterPrint();
                }
            });
        }

        window.onbeforeprint = beforePrint;
        window.onafterprint = afterPrint;
    }
}

export default View;

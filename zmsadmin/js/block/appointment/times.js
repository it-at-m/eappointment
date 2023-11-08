import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.selectedDate = options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.showLoader = options.showLoader || false;
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        //console.log('Component: Appointment Times', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/counter/appointmentTimes/?selecteddate=${this.selectedDate}`
        return this.loadContent(url, 'GET', null, null, this.showLoader);
    }
}

export default View;

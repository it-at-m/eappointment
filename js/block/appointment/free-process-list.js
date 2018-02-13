import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.$main = $(element);
        this.selectedDate = options.selectedDate;
        this.selectedScope = options.selectedScope || 0;
        this.selectedProcess = options.selectedProcess || 0;
        this.selectedTime = options.selectedTime;
        this.slotType = options.slotType;
        this.slotsRequired = options.slotsRequired;
        this.includeUrl = options.includeUrl || "";
        this.bindPublicMethods('loadList');
        $.ajaxSetup({ cache: false });
    }

    loadList() {
        const url = `${this.includeUrl}/appointmentForm/processlist/free/?selecteddate=${this.selectedDate}&selectedtime=${this.selectedTime}&slottype=${this.slotType}&slotsrequired=${this.slotsRequired}&selectedscope=${this.selectedScope}&selectedprocess=${this.selectedProcess}`
        return this.loadContent(url, 'GET', null, null, false).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadErrorCallback(source, url) {
        if (source == 'button') {
            return this.loadContent(url)
        } else if (source == 'lightbox') {
            console.log('lightbox closed without action call');
        } else {
            const defaultUrl = `${this.includeUrl}/counter/`
            return this.loadContent(defaultUrl)
        }
    }
}

export default View;

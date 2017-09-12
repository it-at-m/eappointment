import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        console.log('BUTTONS', element);
        this.selectedDate = options.selectedDate;
        this.selectedProcess = options.selectedProcess || 0;
        this.includeUrl = options.includeUrl || "";
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
    }

    load() {
        const url = `${this.includeUrl}/appointmentForm/buttons/?selecteddate=${this.selectedDate}&selectedprocess=${this.selectedProcess}`
        return this.loadContent(url, 'GET').catch(err => this.loadErrorCallback(err.source, err.url));
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

import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.$main = $(element);
        this.selectedDate = options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.showLoader = options.showLoader || false;
        this.onGhostWorkstationChange = options.onGhostWorkstationChange || (() => {});
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        //console.log('Component: Queue Info', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/counter/queueInfo/?selecteddate=${this.selectedDate}`
        return this.loadContent(url, 'GET', null, null, this.showLoader);
    }

    bindEvents() {
        this.$main.off('click').on('change', 'select[name=count]', (event)=> {
            this.onGhostWorkstationChange(this.$main, event)
        })
    }
}

export default View;

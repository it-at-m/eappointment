import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.$parent = $(element);
        this.selectedDate = options.selectedDate;
        this.selectedProcess = options.selectedProcess || 0;
        this.includeUrl = options.includeUrl || "";
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
    }

    load() {
        this.$main = this.$parent.find('[data-form-buttons]');
        const url = `${this.includeUrl}/appointmentForm/buttons/?selecteddate=${this.selectedDate}&selectedprocess=${this.selectedProcess}`
        return this.loadContent(url, 'GET', null, null, false)
          .then(() => {this.toggleButtonDisabled()})
          .catch(err => this.loadErrorCallback(err.source, err.url)
        );
    }

    toggleButtonDisabled() {
        var freeProcessListLength = this.$parent.find('[data-free-process-list] option').length;
        if (0 == freeProcessListLength) {
            this.$main.find('button').not('.process-abort').prop("disabled",true);
        }
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

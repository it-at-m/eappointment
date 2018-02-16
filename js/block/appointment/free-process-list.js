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
        const slotsCount = $('#appointmentForm_slotCount').val();
        console.log('TODO: MATCH Slot Count with available free Processes', slotsCount);
        const url = `${this.includeUrl}/appointmentForm/processlist/free/?selecteddate=${this.selectedDate}&selectedtime=${this.selectedTime}&slottype=${this.slotType}&slotsrequired=${this.slotsRequired}&slotscount=${slotsCount}&selectedscope=${this.selectedScope}&selectedprocess=${this.selectedProcess}`
        return this.loadContent(url, 'GET', null, null, false).catch(err => this.loadErrorCallback(err));
    }
}

export default View;

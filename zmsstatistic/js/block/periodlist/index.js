import BaseView from "../../lib/baseview"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.bindEvents();
        console.log('Component: Periodlist', this, options);
    }

    bindEvents() {
        this.$main.off('click').on('click', '.report-period--show-all', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.$main.find('.report-period--show-all').hide();
            this.$main.find('.table--base tr').removeClass('hide');
        })
    }
}

export default View;

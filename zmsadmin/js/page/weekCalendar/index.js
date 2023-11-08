import BaseView from '../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.bindPublicMethods('bindEvents');
        $(this.bindEvents);
        console.log('Component: Week Calendar', this, options);
    }

    bindEvents() {
        this.$main.on('change', '.showClientNames input', (ev) => {
            this.toggleClientNames(ev);
        });
    }

    toggleClientNames(ev) {
        var $target = $(ev.target);
        if ($target.prop('checked')) {
            this.$main.find('.calendar-weektable_calendar .familyName').each((index, item) => {
                $(item).show()
            });
        } else {
            this.$main.find('.calendar-weektable_calendar .familyName').each((index, item) => {
                $(item).hide()
            });
        }

    }

}

export default View;

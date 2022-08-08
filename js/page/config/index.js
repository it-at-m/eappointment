import BaseView from '../../lib/baseview'
import { stopEvent } from '../../lib/utils'
import $ from 'jquery'


class View extends BaseView {

    constructor(element, options) {
        super(element);
        this.$main = $(element);
        this.includeUrl = options.includeurl;
        this.bindPublicMethods(
            'bindEvents',
            'onChangeProperty'
        );
        $(this.bindEvents());
    }

    bindEvents() { 
        this.$main.off('click').on('click', 'a.edit', (ev) => {
            this.onChangeProperty(ev, "config_replace_property", () => {});
        });
    }

    onChangeProperty(event, template, callback) {
        stopEvent(event);
        const property = $(event.currentTarget).data('property');
        const value = $(event.currentTarget).data('value');
        const key = $(event.currentTarget).data('key');
        var url = `${this.includeUrl}/dialog/?template=${template}`;
        if (property || value) {
            url = url + `&parameter[property]=${property}&parameter[value]=${value}&parameter[key]=${key}`;
        }
        this.loadCall(url).then((response) => {
            this.loadDialog(response, callback, null, event.currentTarget);
        });
    }
}

export default View;

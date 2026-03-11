import BaseView from '../../lib/baseview'
import $ from 'jquery'
import { stopEvent } from '../../lib/utils'

class View extends BaseView {
    constructor(element, options) {
        super(element);
        this.$main = $(element);
        this.includeUrl = options.includeurl;
        this.bindPublicMethods();
        $(() => {
            this.bindEvents();
        });
    }

    bindEvents() {
        this.$main.off('click').on('click', 'a.button-delete', (ev) => {
            const href = ev.currentTarget.href;
            this.onConfirm(ev, "confirm_role_delete", () => {
                window.location.href = href;
            });
        });
    }

    onConfirm(event, template, callback) {
        stopEvent(event);
        const roleName = $(event.currentTarget).data('name');
        this.loadCall(`${this.includeUrl}/dialog/?template=${template}&parameter[name]=${roleName}`).then((response) => {
            this.loadDialog(response, callback, null, event.currentTarget);
        });
    }
}

export default View;


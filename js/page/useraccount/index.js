import BaseView from '../../lib/baseview'
import $ from 'jquery'
import { stopEvent } from '../../lib/utils'

class View extends BaseView {
    constructor (element, options) {
        super(element);
        this.$main = $(element);
        this.element = $(element).focus();
        this.includeUrl = options.includeurl;
        this.bindPublicMethods();
        $(this.bindEvents());
    }

    bindEvents() {
        this.$main.off('click').on('click', 'a.button-delete', (ev) => {
            this.onConfirm(ev, "confirm_user_delete", () => {this.onDelete(ev)});
        });
    }

    onConfirm(event, template, callback)
    {
      stopEvent(event);
      const userName  = $(event.currentTarget).data('name');
      this.loadCall(`${this.includeUrl}/dialog/?template=${template}&parameter[name]=${userName}`).then((response) => {
           this.loadDialog(response, callback, null, event.currentTarget);
      });
    }

    onDelete(ev) {
        window.location.href = ev.target.href;
    }
}

export default View;

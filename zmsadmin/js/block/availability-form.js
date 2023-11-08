import $ from "jquery";
import BaseView from '../lib/baseview';

class View extends BaseView {

    constructor (element, options) {
        super(element);
        console.log("Form Availability");
        this.bindPublicMethods('save', 'delete');
        this.$.find('button.button-save').on('click', this.save);
        this.$.find('button.button-delete').on('click', this.delete);
        this.options = options
        console.log('this', this)
    }

    save () {
        console.log("Save Button pressed");
        this.$.hide();
        return false;
    }

    delete (ev) {
        ev.preventDefault();
        const id  = this.$.find('input[name=id]').val()
        this.options.removeAvailability(id)

        const ok = confirm('Soll diese Öffnungszeit wirklich gelöscht werden?')

        if (ok) {
            $.ajax(`/availability/delete/${id}/`, {
                method: 'GET'
            }).done(() => {
                this.options.removeAvailability(id)
                this.$.hide();
            }).fail(err => {
                console.log('ajax error', err)
            })
        }
    }
}

export default View;

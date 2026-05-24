import $ from "jquery";
import BaseView from '../lib/baseview';
import { buildConfirmDialogHtml } from '../lib/confirmDialog';

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
        const id = this.$.find('input[name=id]').val();
        const removeAvailability = this.options.removeAvailability;
        const $form = this.$;

        const dialogHtml = buildConfirmDialogHtml(
            'Öffnungszeit löschen',
            'Soll diese Öffnungszeit wirklich gelöscht werden?',
            'Löschen'
        );
        BaseView.loadDialogStatic(
            dialogHtml,
            () => {
                $.ajax(`/availability/delete/${id}/`, {
                    method: 'DELETE'
                }).done(() => {
                    removeAvailability(id);
                    $form.hide();
                }).fail(err => {
                    console.log('ajax error', err);
                });
            },
            () => {},
            { $main: $('body') }
        );
    }
}

export default View;

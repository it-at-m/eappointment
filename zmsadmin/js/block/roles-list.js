import $ from "jquery";
import BaseView from "../lib/baseview";
import { buildConfirmDialogHtml } from "../lib/confirmDialog";

class View extends BaseView {

    constructor(element, options) {
        super(element, options);
        this.$.on('click', '.role-delete', this.onDeleteClick.bind(this));
    }

    onDeleteClick(ev) {
        ev.preventDefault();

        const trigger = ev.currentTarget;
        const dialogHtml = buildConfirmDialogHtml(
            'Rolle löschen',
            'Soll diese Rolle wirklich gelöscht werden?',
            'Löschen'
        );

        BaseView.loadDialogStatic(
            dialogHtml,
            () => {
                $.ajax(trigger.getAttribute('href'), {
                    method: 'DELETE'
                }).done((response, textStatus, xhr) => {
                    window.location.assign(xhr.responseURL || window.location.href);
                }).fail(() => {
                    window.location.assign(window.location.href);
                });
            },
            () => {},
            { $main: $('body') },
            false,
            trigger
        );
    }
}

export default View;

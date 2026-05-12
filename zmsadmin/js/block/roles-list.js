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
                }).done(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('success', 'role_deleted');
                    window.location.assign(url.toString());
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

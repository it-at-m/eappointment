/* global confirm */
import $ from "jquery";
import BaseView from '../../../lib/baseview';

const loaderHtml = '<div class="loader-small"></div>'

class View extends BaseView {

    constructor (element) {
        super(element);
        console.log("Queue Table");
        this.bindPublicMethods('edit', 'delete');
        this.$.find('a.process-edit').on('click', this.edit);
        this.$.find('a.process-delete').on('click', this.delete);
    }

    edit () {
        console.log("Edit Button pressed");
        this.$.hide();
        return false;
    }

    delete (ev) {
        ev.preventDefault();
        const id  = $(ev.target).data('id')
        const authkey  = $(ev.target).data('authkey')
        const name  = $(ev.target).data('name')
        const ok = confirm('Wenn Sie den Kunden Nr. '+ id +' '+ name +' löschen wollen, klicken Sie auf OK. Der Kunde wird darüber per eMail und/oder SMS informiert.)')
        const url = `${this.includeUrl}/process/${id}/${authkey}/delete/`;
        if (ok) {
            $(ev.target).hide();
            $(ev.target).closest('td').append(loaderHtml);
            $.ajax(url, {
                method: 'GET'
            }).done(() => {
                $(ev.target).closest('tr').fadeOut('slow', function(){
                    $(ev.target).remove();
                });
            }).fail(err => {
                console.log('ajax error', err);
            })
        }
    }
}

export default View;

import BaseView from '../../lib/baseview';
import $ from "jquery";

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods("change");
        this.$.find('select#provider__id').change(this.change);
    }

    change (event) {
        let contact = this.$.find('#provider__id option:selected').data('contact');
        this.$.find('input[name="contact[name]"]').val(contact.name);
        this.$.find('input[name="contact[street]"]').val(contact.street + " " + contact.streetNumber);
    }
}

export default View;

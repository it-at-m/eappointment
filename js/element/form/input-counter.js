
import BaseView from '../../lib/baseview';
import $ from "jquery";

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods("change");
        this.$.on('click', this.change);
        this.$.find('a.decrement').on('click', this.decrement);
    }

    change (event) {
        let number = this.$.find('input').val();
        let $input = this.$.find('input');
        if ($(event.target).hasClass('decrement')) {
            $input.val(number > 0 ? --number : 0);
        } else {
            $input.val(++number);
        }
        return false;
    }
}

export default View;


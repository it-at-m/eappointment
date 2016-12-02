
import BaseView from '../lib/baseview';

import DatePickerView from './form/datepicker';
import InputCounterView from './form/input-counter';

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods();
        this.$.find('.add-date-picker').each(function() {new DatePickerView(this);});
        this.$.find('.form-input-counter').each(function() {new InputCounterView(this);});
    }
}

export default View;

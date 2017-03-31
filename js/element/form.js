
import BaseView from '../lib/baseview';

import DatePickerView from './form/datepicker';
import InputCounterView from './form/input-counter';
import ScopeFormView from './form/scope';

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods();
        this.$.find('.add-date-picker').each(function() {new DatePickerView(this);});
        this.$.find('.form-input-counter').each(function() {new InputCounterView(this);});
        this.$.find('.scope-form-update').each(function() {new ScopeFormView(this);});
    }
}

export default View;

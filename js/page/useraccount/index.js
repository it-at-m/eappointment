import BaseView from '../../lib/baseview'
import $ from 'jquery'
import UserFormView from '../../block/useraccount/userForm'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.selectedUserId = options.selecteduserid || 'add';
        this.bindPublicMethods('loadForm', 'onSaveProcess');
        this.$.ready(this.loadForm);
        $.ajaxSetup({ cache: false });
        console.log('Component: Useraccount Edit', this, options);
    }

    bindEvents() {
    }

    loadForm() {
        return new UserFormView(this.$main.find('.useraccount-edit'), {
            onSaveProcess: this.onSaveProcess,
            includeUrl: this.includeUrl,
            dataUrl: `${this.includeUrl}/useraccount/${this.selectedUserId}/`
        })
    }

    onSaveProcess () {
        const loginname = this.$main.find('[name=id]').val();
        //redirect to useraccount edit page
        this.locationLoad(`${this.includeUrl}/useraccount/${loginname}/`)
    }
}

export default View;

import BaseView from '../../lib/baseview'
import $ from 'jquery'
import UserFormView from '../../block/useraccount/userForm'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.bindPublicMethods('loadChangePasswordForm', 'onSaveProcess');
        this.$.ready(this.loadChangePasswordForm);
        $.ajaxSetup({ cache: false });
        console.log('Component: Profile', this, options);
    }

    bindEvents() {
    }

    loadChangePasswordForm() {
        return new UserFormView(this.$main.find('[data-useraccount-changepassword]'), {
            onSaveProcess: this.onSaveProcess,
            includeUrl: this.includeUrl,
            dateUrl: `${this.includeUrl}/profile/`
        })
    }

    onSaveProcess () {
        this.cleanReload();
    }
}

export default View;

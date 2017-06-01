import BaseView from '../../lib/baseview'
import $ from 'jquery'
import ChangePasswordView from '../../block/useraccount/changePassword'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.bindPublicMethods('loadChangePasswordForm');
        this.$.ready(this.loadChangePasswordForm);
        $.ajaxSetup({ cache: false });
        console.log('Component: Profile', this, options);
    }

    bindEvents() {
    }

    loadChangePasswordForm() {
        return new ChangePasswordView(this.$main.find('[data-useraccount-changepassword]'), {
            onSaveProcess: this.onSaveProcess,
            includeUrl: this.includeUrl
        })
    }

    onSaveProcess () {
    }
}

export default View;

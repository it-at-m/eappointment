import BaseView from '../../lib/baseview'
import $ from 'jquery'
import { stopEvent } from '../../lib/utils'

class View extends BaseView {
    constructor(element, options) {
        super(element);
        this.$main = $(element);
        this.includeUrl = options.includeurl;
        this.randomPassword = this.createRandomPassword();
        this.bindPublicMethods();
        $(() => {
            this.bindEvents();
            this.adjustCredentialsToOidc();
        });
    }

    bindEvents() {
        this.$main.off('click').on('click', 'a.button-delete', (ev) => {
            this.onConfirm(ev, "confirm_user_delete", () => { this.onDelete(ev) });
        }).on('change', '#useOidcProvider', () => {
            this.adjustCredentialsToOidc();
        });
    }

    onConfirm(event, template, callback) {
        stopEvent(event);
        const userName = $(event.currentTarget).data('name');
        this.loadCall(`${this.includeUrl}/dialog/?template=${template}&parameter[name]=${userName}`).then((response) => {
            this.loadDialog(response, callback, null, event.currentTarget);
        });
    }

    onDelete(ev) {
        window.location.href = ev.target.href;
    }

    adjustCredentialsToOidc() {
        const $oidcSelect = this.$main.find('#useOidcProvider');
        const $passwordInputs = this.$main.find('input[type="password"]');
        
        // If OIDC select does not exist, we're on the edit page
        if (!$oidcSelect.length) {
            // On edit page: disable password fields (won't submit), show masked placeholder for visual indication
            // Keep value empty so nothing gets submitted, but placeholder shows there's a password set
            $passwordInputs.prop('disabled', true).attr('placeholder', '••••••••').val('');
            return;
        }
        
        // On add page: fill with random password if OIDC is selected
        const oidcSelected = $oidcSelect.val() !== '';
        $passwordInputs.each((index, item) => {
            const $item = $(item);
            if (oidcSelected) {
                $item.prop('readonly', true).val(this.randomPassword);
            } else {
                $item.prop('readonly', false).val('');
            }
        });
    }

    createRandomPassword() {
        var password = '';
        var str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' +
            'abcdefghijklmnopqrstuvwxyz0123456789@#$';
        for (let i = 1; i <= 8; i++) {
            var char = Math.floor(Math.random()
                * str.length + 1);

            password += str.charAt(char)
        }
        return password;
    }
}

export default View;

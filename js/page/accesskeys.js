import BaseView from "../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.$.ready(() => {
            this.bindKeyEvents();
        });
        //  console.log('Component: LoginForm', this, options);
    }

    bindKeyEvents() {
        const $main = this.$main;
        const keySelectorMap = {
            'a': 'input[name=email], .button-login',
            'b': 'input[name=sendConfirmation]',
            'g': '.service-checkbox:first',
            'h': '.process-queue',
            'i': 'input[name=sendMailConfirmation]',
            'm': 'input[id=process_date]',
            'n': 'input[name=loginName], input[name=familyName]',
            'p': 'input[name=workstation], input[name=password]',
            'r': '[data-button-download], select[name=headsUpTime]',
            's': 'select[name=scope]',
            't': 'input[name=telephone]',
            'w': 'textarea[name=amendment]',
            'y': '[data-button-print]',
            'z': '[name=hint], select[name=selectedtime]'
        };

        const focus = function(selectors) {
            var $elms = $main.find(selectors).filter(':visible');//.first();
            console.log($elms);
            if ($elms.length) {
                $elms.first().focus();
                return true; 
            }
            return false;
        };
        const focusKey = function(key) {
            return focus(keySelectorMap[key]);
        };

		$main.off().on('keydown', ':not(input, textarea, select)', (keyEvent) => {
			var isModifierKey = !!keyEvent.metaKey || !!keyEvent.ctrlKey || !!keyEvent.shiftKey;
			var key = keyEvent.key;
			var targetIsInputElement = $(keyEvent.currentTarget).is('input, textarea, select');
			var isReturnKey = key === 13;

			// Only if key isn't a modifier, not enter, not inside an input element and exists as a keyboard shortcut
			if (!targetIsInputElement && !isModifierKey && !isReturnKey && keySelectorMap[key]) {
				if (focusKey(key)) {
					if (key === 'm') {
						$('#process_date').click();
					}   
					console.log('default prevented');
					keyEvent.preventDefault();
				}
			}
		})
    }
}

export default View

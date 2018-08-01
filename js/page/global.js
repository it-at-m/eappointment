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
        console.log('key event');
        const $main = this.$main;
        const keyActions = {
            'a': function () {
                $main.find('.button-login').focus()
                $main.find('input[name=email]').focus()
            },
            'b': function () {
                $main.find('input[name=sendConfirmation]').focus()
            },
            'd': function () {
                $main.find('[data-button-print]').focus()
                $main.find('.service-checkbox').first().focus()
            },
            'e': function () {
                $main.find('[data-button-download]').focus()
                $main.find('select[name=headsUpTime]').focus()
            },
            'h': function () {
                $main.find('.process-queue').focus()
            },
            'i': function () {
                $main.find('input[name=sendMailConfirmation]').focus()
            },
            'm': function () {
                $main.find('input[id=process_date]').focus()
            },
            'n': function () {
                $main.find('input[name=loginName]').focus()
                $main.find('input[name=familyName]').focus()
            },
            'p': function () {
                $main.find('input[name=password]').focus()
                $main.find('input[name=workstation]').focus()
            },
            's': function () {
                $main.find('select[name=scope]').focus()
            },
            't': function () {
                $main.find('input[name=telephone]').focus()
            },
            'w': function () {
                $main.find('textarea[name=amendment]').focus()
            },
            'z': function () {
                $main.find('[name=hint]').focus()
                $main.find('select[name=selectedtime]').focus()
            }
        }
        $main.off().on('keypress', (keyEvent) => {
            var input = $('input');
            if (!input.is(":focus")) {
                keyActions[keyEvent.key]();
            }
        })
    }
}

export default View

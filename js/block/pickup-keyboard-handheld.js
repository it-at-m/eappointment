
import BaseView from '../lib/baseview';
import $ from "jquery";

class View extends BaseView {


    constructor (element) {
        super(element);
        this.minNumberLength = 5;
        this.bindPublicMethods('appendNumber', 'deleteNumber', 'clearNumbers', 'checkNumber');
        console.log("Found pickup-keyboard-handheld");
        this.$.find('button.ziffer').on('click', this.appendNumber);
        this.$.find('button.deleteNumber').on('click', this.deleteNumber);
        this.$.find('button.clearNumbers').on('click', this.clearNumbers);
        this.$numberInput = this.$.find('#Nummer');
    }

    appendNumber (event) {
        let $content = $(event.target).closest('button').find('.number');
        let number = $content.text();
        this.$numberInput.val(this.$numberInput.val() + '' + number);
        this.checkNumber();
        return false;
    }

    deleteNumber () {
        this.$numberInput.val(this.$numberInput.val().replace(/.$/, ''));
        this.checkNumber();
        return false;
    }

    clearNumbers () {
        this.$numberInput.val('');
        this.checkNumber();
        return false;
    }

    checkNumber () {
        console.log(this.$numberInput.val());
        var number = this.$numberInput.val();
        number = number.replace(/^0+/, '');
        number = number.replace(/[^\d]/g, '');
        var $button = this.$.find('.aufrufen');
        if (number.length >= this.minNumberLength) {
            $button.removeClass('disabled').attr('disabled', false);
        } else {
            if (!$button.hasClass('disabled')) {
                $button.addClass('disabled').attr('disabled', true);
            }
        }
        this.$numberInput.val(number);
    }

}

export default View;

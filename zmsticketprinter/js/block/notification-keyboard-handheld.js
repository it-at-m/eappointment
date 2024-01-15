
import BaseView from '../lib/baseview';
import $ from "jquery";

class View extends BaseView {


    constructor (element) {
        super(element);        
        this.source = this.$.attr('id');        
        if (this.source == "waitingNumberPad") {
            this.minNumberLength = 1;
        } else {
            this.minNumberLength = 10;
        }
        
        this.bindPublicMethods('appendNumber', 'deleteNumber', 'clearNumbers', 'checkNumber');
        console.log("Found keyboard-handheld");
        this.$.find('button.ziffer').on('click', this.appendNumber);
        this.$.find('button#removelastdigit').on('click', this.deleteNumber);
        this.$.find('button#removealldigitsphone').on('click', this.clearNumbers);
        this.$numberInput = this.$.find('.nummerneingabe');
    }

    appendNumber (event) {
	let $content = $(event.target).closest('button');
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
        //console.log(this.$numberInput.val());
        var number = this.$numberInput.val();
        if (this.source == 'waitingNumberPad') {
            number = number.replace(/^0+/, '');
        }        
        number = number.replace(/[^\d]/g, '');
        var $button = this.$.find('.nachtrag');
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

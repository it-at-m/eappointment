import BaseView from '../../lib/baseview';
import $ from "jquery";

class View extends BaseView {
    constructor (element) {
        super(element);
        this.$main = $(element);
        this.minNumberLength = 1;
        this.bindPublicMethods('appendNumber', 'deleteNumber', 'clearNumbers', 'checkNumber');
        this.bindEvents();
        this.$numberInput = this.$.find('#Nummer');
        $(this.checkNumber());
    }

    bindEvents() {
      this.$main.off('click').on('click', 'button.ziffer', (event) => {
          this.appendNumber(event)
      }).on('click', 'button.deleteNumber', () => {
          this.deleteNumber()
      }).on('click', 'button.clearNumbers', () => {
          this.clearNumbers()
      });
    }

    appendNumber (event) {
        let $content = $(event.currentTarget).closest('button').find('.number');
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
        var number = this.$numberInput.val();
        number = number.replace(/^0+/, '');
        number = number.replace(/[^\d]/g, '');
        const button = this.$.find('.process-pickup');
        button.prop('disabled', true);
        if (number.length >= this.minNumberLength) {
            button.prop('disabled', false);
        }
        this.$numberInput.val(number);
    }
}

export default View;

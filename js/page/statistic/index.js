import BaseView from '../../lib/baseview'
import $ from 'jquery'


class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.bindPublicMethods('bindEvents','checkCheckboxes','checkInputCounter','toggleButtons');
        console.log('Page: Statistic', this, options);
        this.$.ready(this.toggleButtons);
        this.bindEvents();
    }

    bindEvents() {
        this.$main.off('click')
        .on('change', 'input[type="checkbox"]', () => {
            this.toggleButtons();
        }).on('click', 'div.form-input-counter', (ev) => {
            this.changeInputCounter(ev);
            this.toggleButtons();
        })
    }

    changeInputCounter (ev) {
        let $input = $(ev.currentTarget).find('input');
        let number = $input.val();
        if ($(ev.target).hasClass('decrement')) {
            $input.val(number > 0 ? --number : 0);
        } else {
            $input.val(++number);
        }
        $input.attr('value', $input.val());
        return false;
    }

    checkCheckboxes() {
        let isChecked = false;
        $('input[type="checkbox"]').each((index, item) => {
            if ($(item).prop('checked')) {
                isChecked = true;
            }
        });

        return isChecked;
    }

    checkInputCounter() {
        let isSelected = false;
        $('div.input-counter input').each((index, item) => {
            if ($(item).val() > 0) {
                //console.log($(item).val(), $(item).parent().next('.label').text());
                isSelected = true;
            }
        });
        return isSelected;
    }

    toggleButtons() {
        const statisticEnabled = this.$.find('[data-statistic-enabled]').data('statistic-enabled');
        if (! statisticEnabled || this.checkCheckboxes() || this.checkInputCounter()) {
            $('.client-processed form').find('button:submit').each((index, button) => {
                $(button).prop('disabled', false);
            })
        } else {
            $('.client-processed form').find('button:submit').each((index, button) => {
                $(button).prop('disabled', true);
            })
        }

    }
}

export default View;

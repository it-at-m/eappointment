import BaseView from '../../lib/baseview'
import $ from 'jquery'


class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl;
        this.bindPublicMethods('bindEvents','checkCheckboxes','checkInputCounter','toggleButtons');
        //console.log('Page: Statistic', this, options);
        $(this.toggleButtons);
        this.bindEvents();
    }

    bindEvents() {
        this.$main.off('click')
        .on('change', 'input[type="checkbox"]', (ev) => {
            this.checkboxDeselect(ev)
            this.toggleButtons();
        }).on('click', '.form-input-counter', (ev) => {
            this.changeInputCounterFromButton(ev);
            this.checkboxDeselect()
            this.toggleButtons();
        }).on('change', 'input[role="spinbutton"]', (ev) => {
            this.changeInputCounterFromInput(ev);
            this.checkboxDeselect()
            this.toggleButtons();
        })
    }

    checkboxDeselect(ev) {
        $('input[type="checkbox"]').each((index, item) => {
            $(item).prop('checked', false);
        });
        if (ev) {
            $('.form-input-counter').each((index, item) => {
                $(item).trigger('change');
            });  
            
            $('input[role="spinbutton"]').each((index, item) => {
                $(item).val(0);
                $(item).trigger('change');
            });  
            ev.currentTarget.checked = true;
        }
    }

    changeInputCounterFromInput (ev) {
        let $input = $(ev.currentTarget);
        let $decrementBtn = $(ev.currentTarget).parent().find('.decrement');
        let number = $input.val();
        // Enable / Disable decrement button if on 0 
        if (number == 0) {
            $decrementBtn.prop('disabled', true);
        }
        else {
            $decrementBtn.prop('disabled', false);
        }
        $input.attr('value', number);
        return false;
    }

    changeInputCounterFromButton (ev) {
        let $input = $(ev.currentTarget).find('input');
        if ($input == undefined) {
            $input = $(ev.currentTarget);
        } 
        let $decrementBtn = $(ev.currentTarget).find('.decrement');
        //let $incrementBtn = $(ev.currentTarget).find('.increment');
        let number = $input.val();
        if ($(ev.target).hasClass('decrement')) {
            $input.val(number > 0 ? --number : 0);
        } else if ($(ev.target).hasClass('increment')) {
            $input.val(++number);
        }
        // Enable / Disable decrement button if on 0 
        if ($input.val() == 0) {
            $decrementBtn.prop('disabled', true);
        }
        else {
            $decrementBtn.prop('disabled', false);
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
        $('.input-counter input').each((index, item) => {
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

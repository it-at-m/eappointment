/*
 * DATEPICKER (Flatpickr):
 */

export default function() { 
   
    function buildDatepicker() {
        //console.log('init datepicker');
        // simple datepicker
        $('.datepicker-input').flatpickr({ 
            dateFormat:  'd.m.Y',
            altInput: true,
            altFormat: 'd. M Y',
            minDate: 'today',
            //maxDate: new Date().fp_incr(365*3),
            //locale: languageCodeShort,
            onReady: function(selectedDates, dateStr, instance) {
                // do we have a data attribute to remote a "to"-min-date?
                if (typeof $(instance.input).attr('data-setmindate-field') != 'undefined') {
                    instance.remote_Field = $('#'+$(instance.input).attr('data-setmindate-field'))[0];
                }
            },
            onOpen: function(selectedDates, dateStr, instance) {
                // #1 Formreset fix: We need to reset the hidden input field after a form reset,
                //    so we check the value of the visible field and clear the hidden field if necessary:
                if (!instance._input.value.length && instance.input.value.length) {
                    instance.clear();
                }
            },
            onValueUpdate: function(selectedDates, dateStr, instance) {
                if (typeof instance.remote_Field != 'undefined') {
                    // see #1:
                    if (!instance.remote_Field._flatpickr._input.value.length && instance.remote_Field._flatpickr.input.value.length) {
                        instance.remote_Field._flatpickr.clear();
                    }
                    // set the remote mindate:
                    instance.remote_Field._flatpickr.set('minDate', dateStr);
                }
            }
        });


        // range datepicker build from a form selectfield option (EMS):
        $('option.js-add-selectOptionDatepicker').each( function (index) {
            var self = $(this);
            var mySelect = self.parent(); 
            var valOri = self.val();
            
            // build a wrapper field for flatpickr to show the picker popup
            mySelect.wrap('<div class="js-datepicker-selectoption-input_'+index+'" ></div>'); 
                        
            // add flatpickr to the input field
            //$(this).flatpickr({ 
            var myFlatpickr = new flatpickr('.js-datepicker-selectoption-input_'+index, { 
                dateFormat:  'd. M Y',
                clickOpens: false,
                minDate: 'today',
                maxDate: new Date().fp_incr(365*3),
                mode: 'range',
                locale: languageCodeShort,
                onChange: function(selectedDates, dateStr, instance) {
                    // add an iso formated date
                    var selectedDatesIso = [];                    
                    $.each(selectedDates, function(key, value) {
                        selectedDatesIso.push(value.toISOString());
                    });
                    // update the select option
                    $('option.js-custom-option', mySelect).val(selectedDatesIso.join(','));
                    $('option.js-custom-option', mySelect).text(dateStr);
                },
                onOpen: function(selectedDates, dateStr, instance) {
                    // disable our select to prevent closing it when datepicker is open
                    mySelect.prop('disabled', 'disabled');
                    // add a new option to store the custom date
                    if (! $('.js-custom-option', mySelect).length ) {
                        mySelect.append('<option class="js-custom-option" value=""></option>');
                    }
                },
                onClose: function(selectedDates, dateStr, instance) {
                    // re-enable our select when closing the datepicker
                    mySelect.prop('disabled', false);
                }
            });
            
            // Chrome dont understand click events on selectoptions, so we use the change event
            $(document).on('change', mySelect,function(event) {   
                var $selectedOption = $('option:selected', this);
                if ($selectedOption.hasClass('js-add-selectOptionDatepicker')) {
                    $('option.js-custom-option', mySelect).prop('selected', true);
                    myFlatpickr.open();
                }
            });
        });
        
        
    }
    
    function initDatepicker() {
        //console.log('jsbase '+jsbase); // ToDo: make webpack build flatpickr files
        /*
        // ToDO "include" is not working
        include(jsbase + 'flatpickr4/flatpickr.min.js', function() {
        	if ('en' === languageCodeShort) {
        		buildDatepicker();
        	}
        	else {
                include(jsbase + 'flatpickr4/l10n/' + languageCodeShort + '.js', buildDatepicker);        		
        	}
        }); 
        */
        buildDatepicker();
    }
    
    if ( $('.datepicker-input').length 
         || $('option.js-add-selectOptionDatepicker').length) {
        var languageCode = $('html').attr('lang') || 'de';
        var languageCodeShort = languageCode.substring(0, 2);
        initDatepicker();
    }
    
}

/*
 *  Desktop: Open a popup for our header meta navigation links (the popup is part of the main navigation)
 *  Mobile: The popup is part of the main navigation
 */

import * as FocusTrap from './focus-trap';

export default function() { 
   
    var metanaviWrapper = $('.js-header-metanavi-popup');
    var origFocus; // remember the focused element before modal is opened

    metanaviWrapper.each(function() {

        var popup = $('.js-metanavi-popup');
        var trigger = $('.js-toggler', this);

        // OPEN Popup and set correct position and focus
        function openPopup() {
            origFocus = document.activeElement; // remember focus
            trigger.addClass('active');
            popup.addClass('opened'); // first open popup to make offset work!
            var myPos = metanaviWrapper.offset(); //trigger.offset();
            popup.css({
                'position': 'absolute',
                'right' : '1em',
                'top' : myPos.top,
                'margin-top': '2em',
                'margin-left': '0',
            });
            // focus the first link
            //popup.find('a').eq(0).focus(); // -> by "focustrap" now
            // add global event handler: hide popup when clicking anywhere else except the popup and the trigger
            $(document).on('click.metanavipopup touch.metanavipopup', function(event) {
                if (!$(event.target).parents().addBack().is(trigger)) {
                    closePopup();
                }
            });
            FocusTrap.addFocusTrap(popup);
        }

        // CLOSE Popup and reset position
        function closePopup() {
            if (popup.is(':visible')) {
                trigger.removeClass('active');
                popup.css({
                    'position': 'static',
                    'left': 'auto',
                    'top' : 'auto',
                    'margin-top': '0',
                    'margin-left': '0',
                });
                popup.removeClass('opened');
            }
            // remove global event handler
            $(document).off('click.metanavipopup touch.metanavipopup');
            if (origFocus) {
                origFocus.focus(); // jump back to remembered focus
            }
        }

        // Init navigation  
        function init () {
            if (popup.length && trigger.length) {
                // add aria attibutes
                popup.attr('role','menu');
                //show popup when clicking the trigger
                trigger.on('click touch', function(){
                    var trigger = $(this);
                    var activeStatus = trigger.hasClass('active');
                    trigger.removeClass('active');
                    if (activeStatus) {
                        // close popup when clicking on an activ trigger
                        closePopup();
                    } else {
                        // reopen popup when clicking on an inactiv trigger
                        openPopup();
                    }
                });
                // close popup on ESCAPE key
                popup.bind('keydown', function exitKeyEventListener (event) {
                    var key = event.keyCode || event.which;
                    switch(key) {
                    case 27:
                        // ESACEPE    
                        closePopup();
                        break;
                    }
                });
                // Stop propagation to prevent hiding popup when clicking on it
                popup.bind('click touch', function(event) {
                    event.stopPropagation();
                });   
                // initital close popup
                closePopup();
            }
        }

        init ();

    });

}

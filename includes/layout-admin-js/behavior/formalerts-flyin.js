/*
 *  Automatically open a formalerts "Fly-In" with form errors or alerts (fixed sidebar)
 */

import * as BO from './bo';

export default function() { 

    if (!BO.is_palm()) {

        $('.js-formalerts-flyin').each(function() {

            // wrap content
            $(this).wrapInner( '<div class="formalerts__wrapper"></div>');
            // add trigger button
            $(this).prepend( '<button type="button" class="formalerts__trigger"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>' );

            var self = $(this);
            var popupWrapper = $('.formalerts__wrapper', this);
            var trigger = $('.formalerts__trigger', this);
            var ariaTriggerCloseText = self.data('closetext') || 'Overlay ausblenden';
            var ariaTriggerOpenText = self.data('opentext') || 'Overlay anzeigen';
            var delayedWrapperHide;

            // add aria attributes to elements
            trigger.attr('aria-expanded', false).attr('aria-label', ariaTriggerOpenText);
            popupWrapper.attr('role', 'dialog');

            openPopup(popupWrapper,trigger);

            // Toggle popup when clicking the trigger
            trigger.on('click touch', function(){
                // Save my triggerstatus
                var activeStatus = trigger.hasClass('active');
                // Close ALL popups
                closePopup($('.formalerts__wrapper'), $('.formalerts__trigger'));
                if (activeStatus) {
                    // Close popup when clicking on an activ trigger
                    closePopup(popupWrapper,trigger);
                } else {
                    // Reopen popup when clicking on an inactiv trigger               
                    openPopup(popupWrapper,trigger);
                }
            });

            // CLOSE Popup
            function closePopup(popupWrapper,trigger) {
                clearTimeout(delayedWrapperHide);
                self.removeClass('opened');
                trigger.removeClass('active').attr('aria-expanded', false).attr('aria-label', ariaTriggerOpenText);
                // we need to set display to 'none' because of not tabbing through invisible links inside the help wrapper
                // we need a delay to wait the finish of the out-fade
                delayedWrapperHide = setTimeout(function(){
                    popupWrapper.attr('hidden', 'hidden'); 
                }, 200);
            }

            // OPEN Popup
            function openPopup(popupWrapper,trigger) {
                clearTimeout(delayedWrapperHide);
                popupWrapper.removeAttr('hidden'); 
                // we need a short delay to make css transition work
                var delayedWrapperShow = setTimeout(function(){
                    trigger.addClass('active').attr('aria-expanded', true).attr('aria-label', ariaTriggerCloseText);
                    self.addClass('opened');
                }, 1);
            }

        });
    }
    
}

/*
 *  Open a infotext (i) "Fly-In" for our form elements (fixed sidebar)
 */


export default function() { 
    
    $('.js-infotext-flyin').each(function() {
        // wrap content
        $(this).wrapInner( '<div class="infotext__wrapper"></div>');
        // add open button
        $(this).prepend( '<button type="button" class="infotext__trigger"><i class="fas fa-info-circle" aria-hidden="true"></i></button>' );
        // add close button
        $('.infotext__wrapper',this).append( '<button class="infotext__close" type="button"><i class="fas fa-times" aria-hidden="true"></i></button>' );

        var self = $(this);
        var popupWrapper = $('.infotext__wrapper', this);
        var trigger = $('.infotext__trigger', this);
        var closeButton = $('.infotext__close', this);
        var ariaTriggerCloseText = self.data('closetext') || 'Info ausblenden';
        var ariaTriggerOpenText = self.data('opentext') || 'Info anzeigen';
        var delayedWrapperHide;

        // add aria attributes to elements
        closeButton.attr('aria-label', ariaTriggerCloseText);
        trigger.attr('aria-expanded', false).attr('aria-label', ariaTriggerOpenText);
        popupWrapper.attr('aria-expanded', false).attr('role', 'dialog');

        // Show popup when clicking the trigger
        trigger.on('click touch', function(){
            // Save my triggerstatus
            var activeStatus = trigger.hasClass('active');
            // Close ALL popups
            closePopup($('.infotext__wrapper'), $('.infotext__trigger'));
            if (activeStatus) {
                // Close popup when clicking on an activ trigger
                closePopup(popupWrapper,trigger);
            } else {
                // Reopen popup when clicking on an inactiv trigger               
                openPopup(popupWrapper,trigger);
            }
        });

        closeButton.on('click touch', function(){
            trigger.removeClass('active');
            closePopup(popupWrapper,trigger);
        });

        // CLOSE Popup
        function closePopup(popupWrapper,trigger) {
            clearTimeout(delayedWrapperHide);
            popupWrapper.removeClass('opened').attr('aria-expanded', false);
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
                popupWrapper.addClass('opened').attr('aria-expanded', true);
            }, 1);
        }

        /*
        // Hide popup when clicking anywhere else except the popup and the trigger
        $(document).on('click touch', function(event) {
            if (!$(event.target).parents().addBack().is(trigger)) {
                //popupWrapper.removeClass('opened');
                trigger.removeClass('active');
                closePopup(popupWrapper,trigger);
            }
        });
        */  

    });

}

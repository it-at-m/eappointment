
import * as FocusTrap from './focus-trap';

export default function() { 

    /* SMALLSCREEN: */
    $('.navigation-primary').each(function() {

        var container = $(this);
        var hamburger = $('.js-hamburger');
        var closeButton =  $('.js-close', this);
        var delayedWrapperHide;
        var delayedWrapperShow;
        var transitionTime = '200'; // ms
        var bodyPosition; // lock  body from scroll
        var submenuTriggerArray = $('.js-nav-heading', this);
        var origFocus; // remember the focused element before modal is opened
        var elementParentWithClass = function(element, expectedClass, iteration) {
            if (typeof iteration == 'undefined') {
                iteration = 1;
            }

            let parent = element.parent();
            if (parent.hasClass(expectedClass)) {
                return parent;
            }

            if (iteration == 3) { //fail-safe
                return null;
            }

            return elementParentWithClass(parent, expectedClass, iteration + 1);
        };

        // CLOSE Navigation
        function closeNavi() {
            clearTimeout(delayedWrapperHide);
            clearTimeout(delayedWrapperShow);
            container.removeClass('opened').attr('aria-expanded', false);
            hamburger.removeAttr('aria-expanded');
            //trigger.removeClass('active').attr('aria-expanded', false).attr('aria-label', ariaTriggerOpenText);
            // we need to set display to 'none' because of not tabbing through invisible links inside the help wrapper
            // we need a delay to wait the finish of the out-fade
            delayedWrapperHide = setTimeout(function(){
                container.attr('hidden', 'hidden'); 
                container.hide(); 
                clearTimeout(delayedWrapperHide);
            }, transitionTime);
            unlockBody();
            if (origFocus) {
                // jump back to remembered focus
                origFocus.focus();
            }
        }

        // OPEN Navigation
        function openNavi() {
            origFocus = document.activeElement; // remember focus
            clearTimeout(delayedWrapperHide);
            clearTimeout(delayedWrapperShow);
            container.removeAttr('hidden'); 
            container.show(); 
            hamburger.attr('aria-expanded','true');
            // we need a short delay to make css transition work because of hidden attribute
            delayedWrapperShow = setTimeout(function(){
                //trigger.addClass('active').attr('aria-expanded', true).attr('aria-label', ariaTriggerCloseText);
                container.addClass('opened').attr('aria-expanded', true);
            }, 1);
            lockBody();
            FocusTrap.addFocusTrap(container);
        } 
        
        // Lock body from scrolling
        function lockBody() {
            bodyPosition = $(document).scrollTop(); // remember scroll position
            $('body').css('position', 'fixed').css('top', -bodyPosition);
        }

        // Unlock body from scrolling
        function unlockBody() {
            $('body').css('position', 'initial');
            $(document).scrollTop(bodyPosition); // scroll back to old position
        }

        function toggleSubmenu(index) {
            var element = $(submenuTriggerArray[index]);
            var isExpanded = element.attr('aria-expanded') == 'true';
            // close all submenus
            let jsTargets = $('.js-nav-heading', container);

            jsTargets.attr('aria-expanded','false').each(function(index, element) {
                let targetParent = elementParentWithClass($(element), 'nav__block');
                targetParent.removeClass('open');
            });

            if (!isExpanded) {
                let targetParent = elementParentWithClass(element, 'nav__block');
                // open clicked submenu
                element.attr('aria-expanded','true');
                targetParent.addClass('open');
            }  else {
                let targetParent = elementParentWithClass(element, 'nav__block');
                // close clicked submenu
                element.attr('aria-expanded','false');
                targetParent.removeClass('open');
            }
        }
        
        function submenuClickEventListener(event) {
            var index = submenuTriggerArray.index($(this));
            toggleSubmenu(index);
        }
        
        function submenuKeyEventListener(event) {
            var key = event.key;
            var index = submenuTriggerArray.index($(this));
            var length = submenuTriggerArray.length;
            var newIndex;

            switch(key) {
            case 'Up':
            case "ArrowUp":
                //console.log('arrow up');
                newIndex = (index + length - 1) % length;
                submenuTriggerArray[newIndex].focus();
                event.preventDefault();
                break;
            case 'Down':
            case "ArrowDown":
                //console.log('arrow down');
                newIndex = (index + length + 1) % length;
                submenuTriggerArray[newIndex].focus();
                event.preventDefault();
                break;
            case 'Space':
            case ' ':
                //console.log('space');
                toggleSubmenu(index);
                event.preventDefault();
                break;
            case 'Enter':
                //console.log('enter');
                toggleSubmenu(index);
                event.preventDefault();
                break;
            }
        }

        // Init all submenus
        function initSubmenus() {
            // close all submenus
            //$('.js-nav-heading', this).parent().removeClass('open');
            //$('.js-nav-heading', this).attr('aria-expanded','false');
            // add submenu aria attributes
            $('.js-nav-heading', container).attr('tabindex','0');
            // add event listener
            $('.js-nav-heading', container).on('click touch', submenuClickEventListener);
            $('.js-nav-heading', container).on('keydown', submenuKeyEventListener);
        }

        // Init navigation  
        function init () {
            if (hamburger && hamburger.length && container.length) {
                // set transition-time fitting the timout-time:
                container.css('transition', 'left ' + transitionTime + 'ms ease-in-out');
                // close navi on ESCAPE key
                container.on('keydown', function exitKeyEventListener (event) {
                    var key = event.key;
                    switch(key) {
                        case 'Escape': // ESC
                        //console.log('pressed escape');
                        closeNavi();
                        break;
                    }
                });
                // add an overlay behind the navigation
                container.prepend('<span class="overlay"></span>');
                // set some aria attributes:
                hamburger.attr('aria-haspopup','menu');
                // close click
                closeButton.on('click touch', function(){
                    closeNavi();
                });
                // hamburger click
                hamburger.on('click touch', function(){
                    openNavi();
                });                    

                if (submenuTriggerArray.length) {
                    // init submenu items
                    initSubmenus();
                }

                // initital close navi
                closeNavi();
                
            }
        }
        init ();
        
    });

    //}

}

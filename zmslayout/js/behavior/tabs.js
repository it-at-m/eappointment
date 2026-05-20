/*
 *   This content is licensed according to the W3C Software License at
 *   https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
 *   
 *   based on: https://www.w3.org/TR/wai-aria-practices/examples/tabs/tabs-2/tabs.html
 */


export default function() {

    // "Array.from" not supported in IE; So we use "prototype.slice.call"
    Array.prototype.slice.call(document.querySelectorAll('.js-tabs')).forEach(function (tabcontainer) {
        
        var containerId = tabcontainer.id;
        var tablist = tabcontainer.querySelectorAll('.tabs__tablist')[0];
        var tabs = tabcontainer.querySelectorAll('.tabs__tab');
        var panels = tabcontainer.querySelectorAll('.tabs__panel');
        var activeTab = 0; // Wich tab should be activated at start?
        // For easy reference
        var keys = {
            end: 35,
            home: 36,
            left: 37,
            up: 38,
            right: 39,
            down: 40,
            enter: 13,
            space: 32
        };
        // Add or subtract depending on key pressed
        var direction = {
            37: -1,
            38: -1,
            39: 1,
            40: 1
        };

        init();
    
        function addAttributes (index) {
            // Add attributes to each tab
            tabs[index].setAttribute('role','tab');
            tabs[index].setAttribute('id', containerId + 'Tab' + index);
            tabs[index].setAttribute('aria-controls', containerId + 'Panel' + index);
            tabs[index].setAttribute('tabindex','-1');
            tabs[index].setAttribute('aria-selected','false');
            // Add attributes to each panel
            panels[index].setAttribute('role','tabpanel');
            panels[index].setAttribute('tabindex','0');
            panels[index].setAttribute('id', containerId + 'Panel' + index);
            panels[index].setAttribute('aria-labelledby', containerId + 'Tab' + index);
            panels[index].setAttribute('hidden','hidden');
        }

        function addListeners (index) {
            tabs[index].addEventListener('click', clickEventListener, true);
            tabs[index].addEventListener('keydown', keydownEventListener, true);
            tabs[index].addEventListener('keyup', keyupEventListener, true);
    
            // Build an array with all tabs (<button>s) in it
            tabs[index].index = index;
        }
    
        // When a tab is clicked, activateTab is fired to activate it
        function clickEventListener (event) {
            var tab = event.target;
            activateTab(tab, false);
        }
    
        // Handle keydown on tabs
        function keydownEventListener (event) {
            var key = event.keyCode;
            switch (key) {
            case keys.end:
                event.preventDefault();
                // Activate last tab
                focusLastTab();
                break;
            case keys.home:
                event.preventDefault();
                // Activate first tab
                focusFirstTab();
                break;
            // Up and down are in keydown
            // because we need to prevent page scroll >:)
            case keys.up:
            case keys.down:
                determineOrientation(event);
                break;
            }
        }
    
        // Handle keyup on tabs
        function keyupEventListener (event) {
            var key = event.keyCode;
            switch (key) {
            case keys.left:
            case keys.right:
                determineOrientation(event);
                break;
            case keys.enter:
            case keys.space:
                activateTab(event.target);
                break;
            }
        }
    
        // When a tablist aria-orientation is set to vertical,
        // only up and down arrow should function.
        // In all other cases only left and right arrow function.
        function determineOrientation (event) {
            var key = event.keyCode;
            var vertical = tablist.getAttribute('aria-orientation') == 'vertical';
            var proceed = false;
            if (vertical) {
                if (key === keys.up || key === keys.down) {
                    event.preventDefault();
                    proceed = true;
                }
            }
            else {
                if (key === keys.left || key === keys.right) {
                    proceed = true;
                }
            }
            if (proceed) {
                switchTabOnArrowPress(event);
            }
        }
    
        // Either focus the next, previous, first, or last tab
        // depending on key pressed
        function switchTabOnArrowPress (event) {
            var pressed = event.keyCode;
            if (direction[pressed]) {
                var target = event.target;
                if (target.index !== undefined) {
                    if (tabs[target.index + direction[pressed]]) {
                        tabs[target.index + direction[pressed]].focus();
                    }
                    else if (pressed === keys.left || pressed === keys.up) {
                        focusLastTab();
                    }
                    else if (pressed === keys.right || pressed == keys.down) {
                        focusFirstTab();
                    }
                }
            }
        }
    
        // Activates any given tab panel
        function activateTab (tab, setFocus = true) {   
            // Deactivate all other tabs
            deactivateTabs();
            // Remove tabindex attribute
            tab.removeAttribute('tabindex');
            // Set the tab as selected
            tab.setAttribute('aria-selected', 'true');
            // Get the value of aria-controls (which is an ID)
            var controls = tab.getAttribute('aria-controls');
            // Remove hidden attribute from tab panel to make it visible
            document.getElementById(controls).removeAttribute('hidden');   
            // Set focus when required
            if (setFocus === true) {
                tab.focus();
            }
        }
    
        // Deactivate all tabs and tab panels
        function deactivateTabs () {
            for (var tCount = 0; tCount < tabs.length; tCount++) {
                tabs[tCount].setAttribute('tabindex', '-1');
                tabs[tCount].setAttribute('aria-selected', 'false');
            }
            for (var pCount = 0; pCount < panels.length; pCount++) {
                panels[pCount].setAttribute('hidden', 'hidden');
            }
        }
    
        // Make a guess
        function focusFirstTab () {
            tabs[0].focus();
        }
    
        // Make a guess
        function focusLastTab () {
            tabs[tabs.length - 1].focus();
        }
        
        function init () {
            if (tablist && tabs.length && panels.length) {
                // Bind listeners and set attributes
                for (var count = 0; count < tabs.length; ++count) {
                    addAttributes(count);
                    addListeners(count);
                }
                tablist.setAttribute('role','tablist');
                // Finaly, activate the first tab (or wether we defined) without focus it
                activateTab(tabs[activeTab], false);
            } 
        }

    });

    

    
        
    
}

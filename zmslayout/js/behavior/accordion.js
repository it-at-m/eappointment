/*
*   This content is licensed according to the W3C Software License at
*   https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
*
*   Simple accordion pattern example
*   Gerard K. Cohen, 05/20/2017
* 
*   accordion Configuration Options 
*   data-allow-toggle: Allow for each toggle to both open and close individually 
*   data-allow-multiple: Allow for multiple accordion sections to be expanded at the same time (assumes data-allow-toggle otherwise
*      you would not be able to close any of the accordions) 
*   Ex: <dl id="accordionGroup" role="presentation" class="accordion" data-allow-multiple data-allow-toggle> 
* 
*/


export default function() { 
    
       
    // "Array.from" not supported in IE; So we use "prototype.slice.call"
    Array.prototype.slice.call(document.querySelectorAll('.js-accordion')).forEach(function (accordion) {

        var containerId = accordion.id;

        // Allow for multiple accordion sections to be expanded at the same time
        var allowMultiple = (accordion.getAttribute('data-allow-multiple') == 'false')? false : true;

        // Allow for each toggle to both open and close individually
        var allowToggle = (accordion.getAttribute('data-allow-toggle') == 'false')? false : true;

        // Create the array of toggle elements for the accordion group
        var triggers = Array.prototype.slice.call(accordion.querySelectorAll('.accordion__trigger'));
        var panels = Array.prototype.slice.call(accordion.querySelectorAll('.accordion__panel'));

        init();


        function addListeners (index) {
            triggers[index].addEventListener('click', clickEventListener, true);
            triggers[index].addEventListener('keydown', keydownEventListener, true);    
        }

        // When a trigger is clicked, activateTab is fired to activate it
        function clickEventListener (event) {
            var target = event.target;
            if (target.classList.contains('accordion__trigger')) {
                // Check if the current toggle is expanded.
                var isExpanded = target.getAttribute('aria-expanded') == 'true';
                var active = accordion.querySelector('[aria-expanded="true"]');
                // without allowMultiple, close the open accordion
                if (!allowMultiple && active && active.id !== target.id) {
                    // Set the expanded state on the triggering element
                    active.setAttribute('aria-expanded', 'false');
                    // Hide the accordion sections, using aria-controls to specify the desired section
                    document.getElementById(active.getAttribute('aria-controls')).classList.remove('opened');
                    // no timout here (no transition effect on close)
                    document.getElementById(active.getAttribute('aria-controls')).setAttribute('hidden', '');
                }
                if (!isExpanded) {
                    // Set the expanded state on the triggering element
                    target.setAttribute('aria-expanded', 'true');
                    // Hide the accordion sections, using aria-controls to specify the desired section
                    document.getElementById(target.getAttribute('aria-controls')).removeAttribute('hidden');
                    // timout to enable transition effect
                    setTimeout(function(){document.getElementById(target.getAttribute('aria-controls')).classList.add('opened');}, 1);
                }
                else if (allowToggle) {
                    // Set the expanded state on the triggering element
                    target.setAttribute('aria-expanded', 'false');
                    // Hide the accordion sections, using aria-controls to specify the desired section
                    document.getElementById(target.getAttribute('aria-controls')).classList.remove('opened');
                    //timout to enable transition effect
                    setTimeout(function(){document.getElementById(target.getAttribute('aria-controls')).setAttribute('hidden', '');}, 100);
                }
                event.preventDefault();
            }
        }
        
        // Bind keyboard behaviors on the main accordion container
        function keydownEventListener (event) {
            var target = event.target;
            var key = event.which.toString();
            // 33 = Page Up, 34 = Page Down
            var ctrlModifier = (event.ctrlKey && key.match(/33|34/));

            // Is this coming from an accordion header?
            if (target.classList.contains('accordion__trigger')) {
                // Up/ Down arrow and Control + Page Up/ Page Down keyboard operations
                // 38 = Up, 40 = Down
                if (key.match(/38|40/) || ctrlModifier) {
                    var index = triggers.indexOf(target);
                    var direction = (key.match(/34|40/)) ? 1 : -1;
                    var length = triggers.length;
                    var newIndex = (index + length + direction) % length;
                    triggers[newIndex].focus();
                    event.preventDefault();
                }
                else if (key.match(/35|36/)) {
                    // 35 = End, 36 = Home keyboard operations
                    switch (key) {
                    // Go to first accordion
                    case '36':
                        triggers[0].focus();
                        break;
                        // Go to last accordion
                    case '35':
                        triggers[triggers.length - 1].focus();
                        break;
                    }
                    event.preventDefault();
                }
            }
            else if (ctrlModifier) {
                // Control + Page Up/ Page Down keyboard operations
                // Catches events that happen inside of panels
                panels.forEach(function (panel, index) {
                    if (panel.contains(target)) {
                        triggers[index].focus();
                        event.preventDefault();
                    }
                });
            }
        }

        function addAttributes (index) {
            var isExpanded = triggers[index].getAttribute('aria-expanded');
            // Add attributes to each trigger
            triggers[index].setAttribute('id', containerId + 'Tab' + index);
            triggers[index].parentElement.setAttribute('role','heading');
            triggers[index].parentElement.setAttribute('aria-level','3');
            triggers[index].setAttribute('tabindex','0');
            triggers[index].setAttribute('aria-controls', containerId + 'Panel' + index);
            // Add attributes to each panel
            panels[index].setAttribute('id', containerId + 'Panel' + index);
            panels[index].setAttribute('role','region');
            panels[index].setAttribute('aria-labelledby', containerId + 'Tab' + index);
            panels[index].setAttribute('tabindex','0');
            if (!isExpanded) {
                triggers[index].setAttribute('aria-expanded','false');
                panels[index].setAttribute('hidden','hidden');
                panels[index].classList.remove('opened');
            } else {
                triggers[index].setAttribute('aria-expanded','true');
                panels[index].removeAttribute('hidden');
                panels[index].classList.add('opened');
            }
        }

        function init () {
            if (triggers.length && panels.length) {
                accordion.setAttribute('role','presentation');
                // Bind listeners and set attributes
                for (var count = 0; count < triggers.length; ++count) {
                    addAttributes(count);
                    addListeners(count);
                }
            } 
        }

    });

}


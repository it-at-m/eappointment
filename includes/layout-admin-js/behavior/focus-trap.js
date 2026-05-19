/*
 * Description: A simple "focus trap" to hold the focus inside a modal while tabbing
 * BerlinOnline - Tobias Marciniak
 * 
 * What is a focus trap?: https://hackernoon.com/its-a-focus-trap-699a04d66fb5
 */

export function addFocusTrap(elem) {

    // Get all focusable elements inside our trap container
    var tabbable = elem.find('select, input, textarea, button, a, *[role="button"]');

    // Focus the first element
    if (tabbable.length ) {
        tabbable.filter(':visible').first().focus();
    }

    tabbable.bind('keydown', function (e) {

        if (e.keyCode === 9) { // TAB pressed

            // we need to update the visible last and first focusable elements everytime tab is pressed,
            // because elements can change their visibility
            var firstVisible = tabbable.filter(':visible').first();
            var lastVisible = tabbable.filter(':visible').last();
            
            if (firstVisible && lastVisible) {
  
                if (e.shiftKey && ( $(firstVisible)[0] === $(this)[0] ) ) {
                    // TAB + SHIFT pressed on first visible element
                    e.preventDefault();
                    lastVisible.focus();
                } 
                else if (!e.shiftKey && ( $(lastVisible)[0] === $(this)[0] ) ) {
                    // TAB pressed pressed on last visible element
                    e.preventDefault();
                    firstVisible.focus();
                }
            }
        }
    });

}
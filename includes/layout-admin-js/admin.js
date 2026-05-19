/**
 * BerlinOnline admin layout js bundle
 *
 *
 */

// TODO Add Javascript and handle jquery integration
import BO from './behavior/bo';
import FocusTrap from './behavior/focus-trap';
import stickyheader from './behavior/sticky-header';
import accordion from './behavior/accordion';
import tabs from './behavior/tabs';
import collapse from './behavior/collapse';
import stickytrigger from './behavior/form-actions--sticky-trigger';
import navigationprimary from './behavior/navigation-primary';
import datepicker from './behavior/datepicker';
import infotext from './behavior/form-infotext-flyin';
import hamburger from './behavior/hamburger';
import metanavi from './behavior/header-metanavi-popup';
import formalerts from './behavior/formalerts-flyin';

function loadResources() {
    stickyheader();
    accordion();
    tabs();
    collapse();
    datepicker();
    stickytrigger();
    infotext();
    formalerts();
    hamburger();
    metanavi();
    navigationprimary();
   
}

// If the initial readyState is set to "interactive" (iOS/cached) we can load our resources now,
// else we wait for the readyState to change to "interactive"
if (document.readyState === 'interactive') {
    loadResources();
} else {
    document.addEventListener('readystatechange', function (event) {
        switch (document.readyState) {
        case 'interactive':
            // The document has finished loading. We can now access the DOM elements.
            // But sub-resources such as images, stylesheets and frames are still loading.
            loadResources();
            break;
        }
    });
}

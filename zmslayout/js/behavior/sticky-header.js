
import * as BO from './bo';

export default function() { 

    if (BO.is_palm()) {
        // make header sticky on small screens
        $('body').addClass('sticky-header');
    }

}

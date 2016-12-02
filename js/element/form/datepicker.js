
import BaseView from '../../lib/baseview';
import $ from "jquery";
import settings from '../../settings';

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods();
        this.$.on('click', this.showCalendar);
    }

    showCalendar () {
        console.log("show");
        let animationName = 'spaceInLeft';
        $('.calendar')
            .addClass('magictime ' + animationName)
            .one(settings.animationEnd, () => {
                $('.calendar').removeClass('magictime ' + animationName);
            })
        ;
    }
}

export default View;


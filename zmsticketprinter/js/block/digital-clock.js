import BaseView from '../lib/baseview';
import $ from "jquery";

class DigitalTime extends BaseView {

    constructor(element) {
        super(element);
        this.element = element;
        this.initClock = this.initClock.bind(this);
        this.startClock();
    }

    initClock() {
        var time = new Date();
        var hour = time.getHours();
        var minute = time.getMinutes();
        var second = time.getSeconds();
        var temp = hour < 10 ? '0' + hour : hour;
        temp += (second % 2 === 0 ? ":" : " ");
        temp += minute < 10 ? '0' + minute : minute;
        $(this.element).text(temp);
    }

    startClock() {
        this.initClock();
        setInterval(this.initClock, 1);
    }
}

export default DigitalTime;

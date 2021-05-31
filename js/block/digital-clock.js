import BaseView from '../lib/baseview';
import $ from "jquery";

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods('initClock', 'setInterval');
        console.log("Found digital clock");
        $(function() {this.setInterval});
    }

    initClock () {
        var time=new Date();
        var hour=time.getHours();
        var minute=time.getMinutes();
        var second=time.getSeconds();
        var temp=hour;
        if (second%2) temp+=((minute<10)? ":0" : ":")+minute;
        else temp+=((minute<10)? ":0" : " ")+minute;
        this.$.text(temp);
    }

    setInterval () {
	setInterval(this.initClock, 1000);
    }
}

export default View;

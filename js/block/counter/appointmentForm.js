import BaseView from '../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.element = $(element);
        this.includeUrl = options.includeurl
        this.selecteddate = options.selecteddate
        this.bindPublicMethods('loadData');
        console.log('Component: Appointment Form', this);
        this.$.ready(this.loadData);
        $.ajaxSetup({ cache: false });
    }

    loadData () {
        const url = `${this.includeUrl}/counter/appointmentForm/?selecteddate=${this.selecteddate}`
        return new Promise((resolve, reject) => {
            $.ajax(url, {
                method: 'GET'
            }).done(data => {
                console.log($(data).children());
                //this.element.html();
            }).fail(err => {
                console.log('XHR error', url, err)
                reject(err)
            })
        })
    }
}

export default View;

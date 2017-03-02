import BaseView from '../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.includeUrl = options.includeurl
        this.source = options.source
        this.selecteddate = options.selecteddate
        this.bindPublicMethods('loadData');
        console.log('Found Calendar container');
        this.$.ready(this.loadData);
        $.ajaxSetup({ cache: false });
    }

    loadData () {
        const url = `${this.includeUrl}/calendar/?source=${this.source}&selecteddate=${this.selecteddate}`
        return new Promise((resolve, reject) => {
            $.ajax(url, {
                method: 'GET'
            }).done(data => {
                $( '#calendarPage' ).html( data );
            }).fail(err => {
                console.log('XHR error', url, err)
                reject(err)
            })
        })
    }
}

export default View;

import BaseView from '../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.includeUrl = options.includeurl
        this.source = options.source
        this.selecteddate = options.selecteddate
        this.bindPublicMethods('loadData','navigateCalendar');
        console.log('Component: Month Calendar');
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
                this.navigateCalendar();
            }).fail(err => {
                console.log('XHR error', url, err)
                reject(err)
            })
        })
    }

    navigateCalendar () {
        var dateOptions = $( '#calendarPage .calendarDateData' ).data()
        $( '.calendar .calendar-navigation a' ).each((index, element) => {
            $(element).off('click').on('click', (event) => {
                event.preventDefault();
                if ($(element).hasClass('prev')) {
                    this.selecteddate = dateOptions.prev;
                }
                if ($(element).hasClass('today')) {
                    this.selecteddate = $(element).data('date');
                }
                if ($(element).hasClass('next')) {
                    this.selecteddate = dateOptions.next;
                }
                $( '#calendarPage' ).html( "<div class='loader'></div>" );
                this.loadData();
            });
        });
    }
}

export default View;

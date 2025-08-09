import BaseView from "../../lib/baseview"
import moment from 'moment'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.bindEvents();
        this.initializeDateFields();
        console.log('Component: Datepicker', this, options);
    }

    bindEvents() {
        // Handle form submission for date range
        this.$main.find('#datepicker-form').on('submit', (ev) => {
            ev.preventDefault();
            this.handleDateRangeSubmit();
        });
    }

    handleDateRangeSubmit() {
        const fromDate = this.$main.find('#calendar-date-from').val();
        const toDate = this.$main.find('#calendar-date-until').val();

        console.log('Date range selected:', { from: fromDate, to: toDate });

        if (!fromDate || !toDate) {
            console.warn('Both from and to dates are required');
            return;
        }

        // Validate date format (YYYY-MM-DD)
        if (!this.isValidDateFormat(fromDate) || !this.isValidDateFormat(toDate)) {
            console.warn('Invalid date format. Expected YYYY-MM-DD');
            return;
        }

        // Validate that from date is before to date
        if (moment(fromDate).isAfter(moment(toDate))) {
            console.warn('From date must be before to date');
            return;
        }

        this.redirectToDateRange(fromDate, toDate);
    }

    redirectToDateRange(fromDate, toDate) {
        // Get current URL and add date range parameters
        const currentUrl = window.location.pathname;
        const url = `${currentUrl}?from=${encodeURIComponent(fromDate)}&to=${encodeURIComponent(toDate)}`;
        
        console.log('Redirecting to date range URL:', url);
        window.location.href = url;
    }

    /**
     * Initialize date fields with values from URL parameters
     */
    initializeDateFields() {
        const urlParams = this.getUrlParameters();
        const fromInput = this.$main.find('#calendar-date-from');
        const toInput = this.$main.find('#calendar-date-until');
        
        // Extract from and to parameters from URL
        if (urlParams.from && urlParams.to) {
            // Validate date format before setting
            if (this.isValidDateFormat(urlParams.from) && this.isValidDateFormat(urlParams.to)) {
                fromInput.val(urlParams.from);
                toInput.val(urlParams.to);
                console.log('Using URL date parameters:', { from: urlParams.from, to: urlParams.to });
            } else {
                console.warn('Invalid date format in URL parameters');
            }
        } else {
            console.log('No date parameters in URL, using default values');
        }
    }

    /**
     * Extract URL parameters from current URL
     * @returns {object} Object containing URL parameters
     */
    getUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        const params = {};
        
        for (const [key, value] of urlParams) {
            params[key] = value;
        }
        
        return params;
    }

    /**
     * Validate if the given string is a valid date format (YYYY-MM-DD)
     * 
     * @param {string} date
     * @returns {boolean}
     */
    isValidDateFormat(date) {
        if (!date || typeof date !== 'string') {
            return false;
        }
        
        // Check format with regex
        if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
            return false;
        }
        
        // Validate with moment
        const momentDate = moment(date, 'YYYY-MM-DD', true);
        return momentDate.isValid();
    }
}

export default View;

import BaseView from "../../lib/baseview"
import moment from 'moment'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.bindEvents();
        this.initializeFilters();
        console.log('Component: ReportFilters', this, options);
    }

    bindEvents() {
        // Handle form submission
        this.$main.find('form').on('submit', (ev) => {
            ev.preventDefault();
            this.handleFormSubmit();
        });

        // Handle select all button for scopes
        this.$main.find('#select-all-scopes').on('click', () => {
            this.toggleSelectAll();
        });

        // Update select all button when scope selection changes
        this.$main.find('#scope-select').on('change', () => {
            this.updateSelectAllButton();
        });
    }

    initializeFilters() {
        // Initialize scope selection and date fields from URL parameters
        this.initializeScopeSelection();
        this.initializeDateFields();
    }

    initializeScopeSelection() {
        // Get URL parameters to restore selected scopes
        const urlParams = this.getUrlParameters();
        const scopeSelect = this.$main.find('#scope-select');
        
        if (urlParams.scopes && urlParams.scopes.length > 0) {
            // Clear existing selections
            scopeSelect.find('option').prop('selected', false);
            
            // Select the scopes from URL
            urlParams.scopes.forEach(scopeId => {
                scopeSelect.find(`option[value="${scopeId}"]`).prop('selected', true);
            });
        }
        
        this.updateSelectAllButton();
    }

    initializeDateFields() {
        // Populate date fields from URL parameters if available
        const urlParams = this.getUrlParameters();
        const fromInput = this.$main.find('#calendar-date-from');
        const toInput = this.$main.find('#calendar-date-until');
        
        if (urlParams.from && urlParams.to) {
            if (this.isValidDateFormat(urlParams.from) && this.isValidDateFormat(urlParams.to)) {
                fromInput.val(urlParams.from);
                toInput.val(urlParams.to);
            }
        }
    }

    handleFormSubmit() {
        const selectedScopes = this.getSelectedScopes();
        const fromDate = this.$main.find('#calendar-date-from').val();
        const toDate = this.$main.find('#calendar-date-until').val();

        console.log('Form submitted:', { 
            scopes: selectedScopes, 
            from: fromDate, 
            to: toDate 
        });

        // Validate inputs
        if (!this.validateInputs(selectedScopes, fromDate, toDate)) {
            return;
        }

        // Build URL with parameters
        this.redirectWithFilters(selectedScopes, fromDate, toDate);
    }

    validateInputs(selectedScopes, fromDate, toDate) {
        // Check if at least one scope is selected
        if (selectedScopes.length === 0) {
            alert('Bitte wählen Sie mindestens einen Standort aus.');
            return false;
        }

        // If dates are provided, validate them
        if (fromDate || toDate) {
            if (!fromDate || !toDate) {
                alert('Bitte geben Sie sowohl Start- als auch Enddatum an.');
                return false;
            }

            // Validate date format
            if (!this.isValidDateFormat(fromDate) || !this.isValidDateFormat(toDate)) {
                alert('Ungültiges Datumsformat. Erwartetes Format: JJJJ-MM-TT');
                return false;
            }

            // Validate that from date is before to date
            if (moment(fromDate).isAfter(moment(toDate))) {
                alert('Das Startdatum muss vor dem Enddatum liegen.');
                return false;
            }
        }

        return true;
    }

    getSelectedScopes() {
        const selectedOptions = this.$main.find('#scope-select option:selected');
        return selectedOptions.map(function() {
            return this.value;
        }).get();
    }

    toggleSelectAll() {
        const scopeSelect = this.$main.find('#scope-select');
        const allOptions = scopeSelect.find('option');
        const selectedOptions = scopeSelect.find('option:selected');
        
        if (selectedOptions.length === allOptions.length) {
            // Deselect all
            allOptions.prop('selected', false);
        } else {
            // Select all
            allOptions.prop('selected', true);
        }
        
        this.updateSelectAllButton();
    }

    updateSelectAllButton() {
        const scopeSelect = this.$main.find('#scope-select');
        const allOptions = scopeSelect.find('option');
        const selectedOptions = scopeSelect.find('option:selected');
        const button = this.$main.find('#select-all-scopes');
        
        if (selectedOptions.length === allOptions.length) {
            button.attr('title', 'Alle abwählen');
            button.find('i').removeClass('fa-check-double').addClass('fa-times');
        } else {
            button.attr('title', 'Alle Standorte auswählen');
            button.find('i').removeClass('fa-times').addClass('fa-check-double');
        }
    }

    redirectWithFilters(selectedScopes, fromDate, toDate) {
        const currentUrl = new URL(window.location);
        const searchParams = currentUrl.searchParams;
        
        // Clear existing parameters
        Array.from(searchParams.keys()).forEach(key => {
            if (key.startsWith('scopes[') || key === 'from' || key === 'to') {
                searchParams.delete(key);
            }
        });
        
        // Add scope parameters
        selectedScopes.forEach((scopeId, index) => {
            searchParams.set(`scopes[${index}]`, scopeId);
        });
        
        // Add date parameters if provided
        if (fromDate && toDate) {
            searchParams.set('from', fromDate);
            searchParams.set('to', toDate);
        }
        
        console.log('Redirecting to:', currentUrl.toString());
        window.location.href = currentUrl.toString();
    }

    getUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        const scopes = [];
        
        // Extract scope array parameters (scopes[0], scopes[1], etc.)
        for (const [key, value] of urlParams.entries()) {
            if (key.startsWith('scopes[') && key.endsWith(']')) {
                scopes.push(value);
            }
        }
        
        return {
            scopes: scopes,
            from: urlParams.get('from'),
            to: urlParams.get('to')
        };
    }

    isValidDateFormat(date) {
        if (!date || typeof date !== 'string') return false;
        
        // Check format YYYY-MM-DD
        if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
            return false;
        }
        
        // Check if it's a valid date
        const dateObj = moment(date, 'YYYY-MM-DD', true);
        return dateObj.isValid();
    }
}

export default View

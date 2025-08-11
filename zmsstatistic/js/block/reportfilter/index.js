import BaseView from "../../lib/baseview"
import moment from 'moment'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.bindEvents();
        this.initializeFilters();
        this.setupValidation();
        console.log('Component: ReportFilter', this, options);
    }

    bindEvents() {
        // Handle form submission - find the parent form
        this.$main.closest('form').on('submit', (ev) => {
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
            this.validateAndUpdateButton();
        });

        // Add real-time validation for date fields
        this.$main.find('#calendar-date-from, #calendar-date-until').on('change', () => {
            this.validateAndUpdateButton();
        });
    }

    setupValidation() {
        this.addErrorDisplayElements();
        this.validateAndUpdateButton();
    }

    addErrorDisplayElements() {
        if (!this.$main.find('.scope-error').length) {
            this.$main.find('.scope-picker').append('<div class="scope-error text-danger" style="display:none; margin-top: 5px;"></div>');
        }
        if (!this.$main.find('.date-error').length) {
            this.$main.find('.reportfilter-daterange').after('<div class="date-error text-danger" style="display:none; margin-top: 5px;"></div>');
        }
    }

    validateAndUpdateButton() {
        const selectedScopes = this.getSelectedScopes();
        const fromDate = this.$main.find('#calendar-date-from').val();
        const toDate = this.$main.find('#calendar-date-until').val();
        
        const submitButton = this.$main.find('button[type="submit"]');
        const scopeError = this.$main.find('.scope-error');
        const dateError = this.$main.find('.date-error');
        
        let hasErrors = false;

        scopeError.hide();
        dateError.hide();

        if (selectedScopes.length === 0) {
            scopeError.text('Bitte wählen Sie mindestens einen Standort aus.').show();
            hasErrors = true;
        }
        
        if (fromDate || toDate) {
            if (!fromDate || !toDate) {
                dateError.text('Bitte geben Sie sowohl Start- als auch Enddatum an.').show();
                hasErrors = true;
            } else if (!this.isValidDateFormat(fromDate) || !this.isValidDateFormat(toDate)) {
                dateError.text('Ungültiges Datumsformat. Erwartetes Format: JJJJ-MM-TT').show();
                hasErrors = true;
            } else if (moment(fromDate).isAfter(moment(toDate))) {
                dateError.text('Das Startdatum muss vor dem Enddatum liegen.').show();
                hasErrors = true;
            } else if (moment(fromDate).isAfter(moment()) || moment(toDate).isAfter(moment())) {
                dateError.text('Nur vergangene Daten sind erlaubt. Zukünftige Daten sind nicht zulässig.').show();
                hasErrors = true;
            }
        }
        
        if (hasErrors) {
            submitButton.prop('disabled', true).addClass('btn-disabled');
        } else {
            submitButton.prop('disabled', false).removeClass('btn-disabled');
        }
        
        return !hasErrors;
    }

    initializeFilters() {
        this.initializeScopeSelection();
        this.initializeDateFields();
    }

    initializeScopeSelection() {
        const urlParams = this.getUrlParameters();
        const scopeSelect = this.$main.find('#scope-select');
        
        if (urlParams.scopes && urlParams.scopes.length > 0) {
            scopeSelect.find('option').prop('selected', false);
            
            urlParams.scopes.forEach(scopeId => {
                scopeSelect.find(`option[value="${scopeId}"]`).prop('selected', true);
            });
        }
        
        this.updateSelectAllButton();
    }

    initializeDateFields() {
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

        if (this.validateAndUpdateButton()) {
            this.redirectWithFilters(selectedScopes, fromDate, toDate);
        }
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
            allOptions.prop('selected', false);
        } else {
            allOptions.prop('selected', true);
        }
        
        this.updateSelectAllButton();
        this.validateAndUpdateButton();
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
        
        Array.from(searchParams.keys()).forEach(key => {
            if (key.startsWith('scopes[') || key === 'from' || key === 'to') {
                searchParams.delete(key);
            }
        });
        
        selectedScopes.forEach((scopeId, index) => {
            searchParams.set(`scopes[${index}]`, scopeId);
        });
        
        if (fromDate && toDate) {
            searchParams.set('from', fromDate);
            searchParams.set('to', toDate);
        }

        window.location.href = currentUrl.toString();
    }

    getUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        const scopes = [];
        
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
        return moment(date, 'YYYY-MM-DD', true).isValid();
    }
}

export default View

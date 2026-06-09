import BaseView from "../../lib/baseview"
import moment from 'moment'
import { parseJsonText } from "../../lib/utils"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.allowFutureDates = this.$main.is('[data-allow-future-dates]');
        this.scopeDateBounds = this.parseScopeDateBounds();
        this.pickerScopeIds = this.parsePickerScopeIds();
        this.defaultScopeId = this.$main.attr('data-default-scope-id') || '';
        this.bindEvents();
        this.initializeFilters();
        this.addErrorDisplayElements();
        console.log('Component: ReportFilter', this, options);
    }

    parseScopeDateBounds() {
        const raw = this.$main.attr('data-scope-date-bounds');
        if (!raw) {
            return null;
        }
        const parsed = parseJsonText(raw, 'scope-date-bounds');
        if (parsed === null) {
            console.warn('ReportFilter: invalid scope date bounds');
        }
        return parsed;
    }

    parsePickerScopeIds() {
        const raw = this.$main.attr('data-picker-scope-ids');
        if (!raw) {
            return [];
        }
        const ids = parseJsonText(raw, 'picker-scope-ids');
        if (!Array.isArray(ids)) {
            console.warn('ReportFilter: invalid picker scope ids');
            return [];
        }
        return ids.map(String);
    }

    /**
     * Date limits: selected scopes, else all scopes in the picker (not workstation alone).
     */
    getBoundsScopeIds() {
        const selectedScopes = this.getSelectedScopes();
        if (selectedScopes.length > 0) {
            return selectedScopes;
        }
        if (this.pickerScopeIds.length > 0) {
            return this.pickerScopeIds;
        }
        return this.defaultScopeId ? [this.defaultScopeId] : [];
    }

    bindEvents() {
        this.$main.closest('form').on('submit', (ev) => {
            ev.preventDefault();
            this.handleFormSubmit();
        });

        this.$main.find('#select-all-scopes').on('click', () => {
            this.toggleSelectAll();
        });

        this.$main.find('#scope-select').on('change', () => {
            this.updateSelectAllButton();
            this.updateDateRangeLimits();
        });

        this.setupDateRangeLimiting();
    }

    addErrorDisplayElements() {
        if (!this.$main.find('.scope-error').length) {
            this.$main.find('.scope-picker').append('<div class="scope-error text-danger" style="display:none; margin-top: 5px;"></div>');
        }
        if (!this.$main.find('.date-error').length) {
            this.$main.find('.reportfilter-daterange').after('<div class="date-error text-danger" style="display:none; margin-top: 5px;"></div>');
        }
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
        
        this.updateDateRangeLimits();
    }

    setupDateRangeLimiting() {
        const fromInput = this.$main.find('#calendar-date-from');
        const toInput = this.$main.find('#calendar-date-until');
        
        this.updateDateRangeLimits();
        
        fromInput.on('change', () => {
            this.updateDateRangeLimits();
        });
        
        toInput.on('change', () => {
            this.updateDateRangeLimits();
        });
    }

    getTodayIso() {
        return moment().format('YYYY-MM-DD');
    }

    getMaxRangeEndIso(fromIso) {
        return moment(fromIso, 'YYYY-MM-DD').add(366, 'days').format('YYYY-MM-DD');
    }

    getMinRangeStartIso(toIso) {
        return moment(toIso, 'YYYY-MM-DD').subtract(366, 'days').format('YYYY-MM-DD');
    }

    getActiveScopeDateBounds() {
        if (!this.scopeDateBounds) {
            return null;
        }

        const scopeIds = this.getBoundsScopeIds();

        if (scopeIds.length === 0) {
            return null;
        }

        let min = null;
        let max = null;

        scopeIds.forEach((scopeId) => {
            const entry = this.scopeDateBounds[String(scopeId)];
            if (!entry || !entry.min || !entry.max) {
                return;
            }
            min = min === null ? entry.min : moment.max(moment(min), moment(entry.min)).format('YYYY-MM-DD');
            max = max === null ? entry.max : moment.min(moment(max), moment(entry.max)).format('YYYY-MM-DD');
        });

        if (min === null || max === null) {
            return null;
        }

        if (moment(min).isAfter(moment(max))) {
            return null;
        }

        return { min, max };
    }

    setDateInputBound($input, attribute, value) {
        if (value) {
            $input.attr(attribute, value);
        } else {
            $input.removeAttr(attribute);
        }
    }

    clampIso(iso, minIso, maxIso) {
        let value = moment(iso, 'YYYY-MM-DD');
        if (minIso) {
            value = moment.max(value, moment(minIso, 'YYYY-MM-DD'));
        }
        if (maxIso) {
            value = moment.min(value, moment(maxIso, 'YYYY-MM-DD'));
        }
        return value.format('YYYY-MM-DD');
    }

    updateDateRangeLimits() {
        const fromInput = this.$main.find('#calendar-date-from');
        const toInput = this.$main.find('#calendar-date-until');
        const todayIso = this.getTodayIso();
        const apiBounds = this.allowFutureDates ? this.getActiveScopeDateBounds() : null;
        const apiMin = apiBounds ? apiBounds.min : null;
        const apiMax = apiBounds ? apiBounds.max : null;
        let globalMax = this.allowFutureDates
            ? (apiMax || null)
            : (apiMax ? moment.min(moment(apiMax), moment(todayIso)).format('YYYY-MM-DD') : todayIso);
        const globalMin = apiMin || null;

        if (this.allowFutureDates && !globalMax) {
            globalMax = null;
        }

        this.setDateInputBound(fromInput, 'min', globalMin);
        this.setDateInputBound(fromInput, 'max', globalMax);
        this.setDateInputBound(toInput, 'min', globalMin);
        this.setDateInputBound(toInput, 'max', globalMax);

        if (fromInput.val()) {
            const maxToIso = this.getMaxRangeEndIso(fromInput.val());
            let maxAllowedIso = this.allowFutureDates
                ? maxToIso
                : moment.min(moment(maxToIso), moment(todayIso)).format('YYYY-MM-DD');
            if (globalMax) {
                maxAllowedIso = moment.min(moment(maxAllowedIso), moment(globalMax)).format('YYYY-MM-DD');
            }

            let minToIso = fromInput.val();
            if (globalMin) {
                minToIso = moment.max(moment(minToIso), moment(globalMin)).format('YYYY-MM-DD');
            }

            this.setDateInputBound(toInput, 'min', minToIso);
            this.setDateInputBound(toInput, 'max', maxAllowedIso);

            if (toInput.val()) {
                toInput.val(this.clampIso(toInput.val(), minToIso, maxAllowedIso));
            }
        } else {
            this.setDateInputBound(toInput, 'min', globalMin);
            this.setDateInputBound(toInput, 'max', globalMax);
        }

        if (toInput.val()) {
            let minFromIso = this.getMinRangeStartIso(toInput.val());
            if (globalMin) {
                minFromIso = moment.max(moment(minFromIso), moment(globalMin)).format('YYYY-MM-DD');
            }

            let maxFromIso = toInput.val();
            if (globalMax) {
                maxFromIso = moment.min(moment(maxFromIso), moment(globalMax)).format('YYYY-MM-DD');
            }

            this.setDateInputBound(fromInput, 'min', minFromIso);
            this.setDateInputBound(fromInput, 'max', maxFromIso);

            if (fromInput.val()) {
                fromInput.val(this.clampIso(fromInput.val(), minFromIso, maxFromIso));
            }
        } else {
            this.setDateInputBound(fromInput, 'min', globalMin);
            this.setDateInputBound(fromInput, 'max', globalMax);
        }

        if (globalMin || globalMax) {
            if (fromInput.val()) {
                fromInput.val(this.clampIso(fromInput.val(), globalMin, globalMax));
            }
            if (toInput.val()) {
                toInput.val(this.clampIso(toInput.val(), globalMin, globalMax));
            }
        }
    }

    handleFormSubmit() {
        const selectedScopes = this.getSelectedScopes();
        const fromDate = this.$main.find('#calendar-date-from').val();
        const toDate = this.$main.find('#calendar-date-until').val();
        
        const scopeError = this.$main.find('.scope-error');
        const dateError = this.$main.find('.date-error');
        
        scopeError.hide();
        dateError.hide();
        
        if (selectedScopes.length === 0) {
            scopeError.text('Bitte wählen Sie mindestens einen Standort aus.').show();
            return;
        }

        let hasDateErrors = false;
        if (fromDate || toDate) {
            if (!fromDate || !toDate) {
                dateError.text('Bitte geben Sie sowohl Start- als auch Enddatum an.').show();
                hasDateErrors = true;
            } else if (!this.isValidDateFormat(fromDate) || !this.isValidDateFormat(toDate)) {
                dateError.text('Ungültiges Datumsformat. Erwartetes Format: JJJJ-MM-TT').show();
                hasDateErrors = true;
            } else if (moment(fromDate).isAfter(moment(toDate))) {
                dateError.text('Das Startdatum muss vor dem Enddatum liegen.').show();
                hasDateErrors = true;
            } else if (
                !this.allowFutureDates
                && (moment(fromDate).isAfter(moment(), 'day') || moment(toDate).isAfter(moment(), 'day'))
            ) {
                dateError.text('Nur vergangene Daten sind erlaubt. Zukünftige Daten sind nicht zulässig.').show();
                hasDateErrors = true;
            } else if (moment(toDate).diff(moment(fromDate), 'days') > 366) {
                dateError.text('Der gewählte Zeitraum darf höchstens 366 Tage umfassen.').show();
                hasDateErrors = true;
            } else if (this.allowFutureDates) {
                const apiBounds = this.getActiveScopeDateBounds();
                if (apiBounds && (fromDate < apiBounds.min || toDate > apiBounds.max)) {
                    dateError.text(
                        `Der Zeitraum muss zwischen ${moment(apiBounds.min).format('DD.MM.YYYY')} `
                        + `und ${moment(apiBounds.max).format('DD.MM.YYYY')} liegen.`
                    ).show();
                    hasDateErrors = true;
                }
            }
        }
        
        if (hasDateErrors) {
            return;
        }

        this.redirectWithFilters(selectedScopes, fromDate, toDate);
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

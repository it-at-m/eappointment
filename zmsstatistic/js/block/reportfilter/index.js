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
        const serializedScopeDateBounds = this.$main.attr('data-scope-date-bounds');
        if (!serializedScopeDateBounds) {
            return null;
        }
        const scopeDateBoundsByScopeId = parseJsonText(serializedScopeDateBounds, 'scope-date-bounds');
        if (scopeDateBoundsByScopeId === null) {
            console.warn('ReportFilter: invalid scope date bounds');
        }
        return scopeDateBoundsByScopeId;
    }

    parsePickerScopeIds() {
        const serializedPickerScopeIds = this.$main.attr('data-picker-scope-ids');
        if (!serializedPickerScopeIds) {
            return [];
        }
        const pickerScopeIds = parseJsonText(serializedPickerScopeIds, 'picker-scope-ids');
        if (!Array.isArray(pickerScopeIds)) {
            console.warn('ReportFilter: invalid picker scope ids');
            return [];
        }
        return pickerScopeIds.map(String);
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

        let earliestSelectableDate = null;
        let latestSelectableDate = null;

        scopeIds.forEach((scopeId) => {
            const scopeDateBounds = this.scopeDateBounds[String(scopeId)];
            if (!scopeDateBounds || !scopeDateBounds.min || !scopeDateBounds.max) {
                return;
            }
            earliestSelectableDate = earliestSelectableDate === null
                ? scopeDateBounds.min
                : moment.max(moment(earliestSelectableDate), moment(scopeDateBounds.min)).format('YYYY-MM-DD');
            latestSelectableDate = latestSelectableDate === null
                ? scopeDateBounds.max
                : moment.min(moment(latestSelectableDate), moment(scopeDateBounds.max)).format('YYYY-MM-DD');
        });

        if (earliestSelectableDate === null || latestSelectableDate === null) {
            return null;
        }

        if (moment(earliestSelectableDate).isAfter(moment(latestSelectableDate))) {
            return null;
        }

        return {
            earliestSelectableDate,
            latestSelectableDate,
        };
    }

    setDateInputBound($input, htmlAttributeName, value) {
        if (value) {
            $input.attr(htmlAttributeName, value);
        } else {
            $input.removeAttr(htmlAttributeName);
        }
    }

    clampIsoDate(dateIso, minimumDateIso, maximumDateIso) {
        let clampedDate = moment(dateIso, 'YYYY-MM-DD');
        if (minimumDateIso) {
            clampedDate = moment.max(clampedDate, moment(minimumDateIso, 'YYYY-MM-DD'));
        }
        if (maximumDateIso) {
            clampedDate = moment.min(clampedDate, moment(maximumDateIso, 'YYYY-MM-DD'));
        }
        return clampedDate.format('YYYY-MM-DD');
    }

    updateDateRangeLimits() {
        const fromInput = this.$main.find('#calendar-date-from');
        const toInput = this.$main.find('#calendar-date-until');
        const todayIso = this.getTodayIso();
        const combinedScopeDateBounds = this.allowFutureDates ? this.getActiveScopeDateBounds() : null;
        const earliestWarehouseBoundDate = combinedScopeDateBounds
            ? combinedScopeDateBounds.earliestSelectableDate
            : null;
        const latestWarehouseBoundDate = combinedScopeDateBounds
            ? combinedScopeDateBounds.latestSelectableDate
            : null;
        let pickerMaximumDate = this.allowFutureDates
            ? (latestWarehouseBoundDate || null)
            : (latestWarehouseBoundDate
                ? moment.min(moment(latestWarehouseBoundDate), moment(todayIso)).format('YYYY-MM-DD')
                : todayIso);
        const pickerMinimumDate = earliestWarehouseBoundDate || null;

        if (this.allowFutureDates && !pickerMaximumDate) {
            pickerMaximumDate = null;
        }

        this.setDateInputBound(fromInput, 'min', pickerMinimumDate);
        this.setDateInputBound(fromInput, 'max', pickerMaximumDate);
        this.setDateInputBound(toInput, 'min', pickerMinimumDate);
        this.setDateInputBound(toInput, 'max', pickerMaximumDate);

        if (fromInput.val()) {
            const maxToDateIso = this.getMaxRangeEndIso(fromInput.val());
            let maximumAllowedToDateIso = this.allowFutureDates
                ? maxToDateIso
                : moment.min(moment(maxToDateIso), moment(todayIso)).format('YYYY-MM-DD');
            if (pickerMaximumDate) {
                maximumAllowedToDateIso = moment.min(
                    moment(maximumAllowedToDateIso),
                    moment(pickerMaximumDate)
                ).format('YYYY-MM-DD');
            }

            let minimumAllowedToDateIso = fromInput.val();
            if (pickerMinimumDate) {
                minimumAllowedToDateIso = moment.max(
                    moment(minimumAllowedToDateIso),
                    moment(pickerMinimumDate)
                ).format('YYYY-MM-DD');
            }

            this.setDateInputBound(toInput, 'min', minimumAllowedToDateIso);
            this.setDateInputBound(toInput, 'max', maximumAllowedToDateIso);

            if (toInput.val()) {
                toInput.val(this.clampIsoDate(
                    toInput.val(),
                    minimumAllowedToDateIso,
                    maximumAllowedToDateIso
                ));
            }
        } else {
            this.setDateInputBound(toInput, 'min', pickerMinimumDate);
            this.setDateInputBound(toInput, 'max', pickerMaximumDate);
        }

        if (toInput.val()) {
            let minimumAllowedFromDateIso = this.getMinRangeStartIso(toInput.val());
            if (pickerMinimumDate) {
                minimumAllowedFromDateIso = moment.max(
                    moment(minimumAllowedFromDateIso),
                    moment(pickerMinimumDate)
                ).format('YYYY-MM-DD');
            }

            let maximumAllowedFromDateIso = toInput.val();
            if (pickerMaximumDate) {
                maximumAllowedFromDateIso = moment.min(
                    moment(maximumAllowedFromDateIso),
                    moment(pickerMaximumDate)
                ).format('YYYY-MM-DD');
            }

            this.setDateInputBound(fromInput, 'min', minimumAllowedFromDateIso);
            this.setDateInputBound(fromInput, 'max', maximumAllowedFromDateIso);

            if (fromInput.val()) {
                fromInput.val(this.clampIsoDate(
                    fromInput.val(),
                    minimumAllowedFromDateIso,
                    maximumAllowedFromDateIso
                ));
            }
        } else {
            this.setDateInputBound(fromInput, 'min', pickerMinimumDate);
            this.setDateInputBound(fromInput, 'max', pickerMaximumDate);
        }

        if (pickerMinimumDate || pickerMaximumDate) {
            if (fromInput.val()) {
                fromInput.val(this.clampIsoDate(fromInput.val(), pickerMinimumDate, pickerMaximumDate));
            }
            if (toInput.val()) {
                toInput.val(this.clampIsoDate(toInput.val(), pickerMinimumDate, pickerMaximumDate));
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
                const combinedScopeDateBounds = this.getActiveScopeDateBounds();
                if (
                    combinedScopeDateBounds
                    && (
                        fromDate < combinedScopeDateBounds.earliestSelectableDate
                        || toDate > combinedScopeDateBounds.latestSelectableDate
                    )
                ) {
                    dateError.text(
                        `Der Zeitraum muss zwischen ${moment(combinedScopeDateBounds.earliestSelectableDate).format('DD.MM.YYYY')} `
                        + `und ${moment(combinedScopeDateBounds.latestSelectableDate).format('DD.MM.YYYY')} liegen.`
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

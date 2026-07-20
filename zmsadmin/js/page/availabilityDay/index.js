import React, { Component } from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import moment from 'moment'
import validate, { hasBlockingErrors } from './form/validate'
import Conflicts from './conflicts'
import TabsBar from './tabsbar'
import GraphView from './timetable/graphview.js'
import TableView from './timetable/tableview.js'
import SaveBar from './saveBar'
import AccordionLayout from './layouts/accordion'
import PageLayout from './layouts/page'
import { inArray, showSpinner, hideSpinner } from '../../lib/utils'
import ExceptionHandler from '../../lib/exceptionHandler';
import BaseView from '../../lib/baseview';
import { buildConfirmDialogHtml } from '../../lib/confirmDialog';

import {
    getInitialState,
    getStateFromProps,
    writeSlotCalculationIntoAvailability,
    getNewAvailability,
    mergeAvailabilityListIntoState,
    updateAvailabilityInState,
    cleanupAvailabilityForSave,
    deleteAvailabilityInState,
    findAvailabilityInStateByKind,
    formatTimestampDate
} from "./helpers"

const tempId = (() => {
    let lastId = -1

    return () => {
        lastId += 1
        return `__temp__${lastId}`
    }
})()

class AvailabilityPage extends Component {
    constructor(props) {
        super(props)
        this.state = getInitialState(props)
        this.waitintervall = 1000;
        this.errorElement = null;
        this.successElement = null;
        this.isCreatingExclusion = false
        this.setErrorRef = element => {
            this.errorElement = element
        };
        this.setSuccessRef = element => {
            this.successElement = element
        };
    }

    componentDidMount() {
        this.unloadHandler = ev => {
            const confirmMessage = "Es wurden nicht alle Änderungen gespeichert. Diese gehen beim schließen verloren."
            if (this.state.stateChanged) {
                ev.returnValue = confirmMessage
                return confirmMessage
            }
        }

        window.addEventListener('beforeunload', this.unloadHandler)
    }

    componentWillUnmount() {
        window.removeEventListener('beforeunload', this.unloadHandler)
    }

    onPublishAvailability() {
        this.getValidationList({ scrollToErrors: true }).then((errorList) => {
            if (hasBlockingErrors(errorList, this.state.availabilitylist)) {
                const firstError = Object.values(errorList || {})[0];
                const invalidAvailability = firstError
                    ? this.state.availabilitylist.find(availability =>
                        (availability.id && availability.id === firstError.id) ||
                        (availability.tempId && availability.tempId === firstError.id)
                    )
                    : null;

                if (invalidAvailability) {
                    this.setState({ selectedAvailability: invalidAvailability }, () => {
                        this.errorElement?.scrollIntoView();
                    });
                } else {
                    this.errorElement?.scrollIntoView();
                }

                return;
            }

            this.onSaveUpdates();
        }).catch(() => {});
    }

    refreshData() {
        const currentDate = formatTimestampDate(this.props.timestamp)
        const url = `${this.props.links.includeurl}/scope/${this.props.scope.id}/availability/day/${currentDate}/conflicts/`

        return new Promise((resolve) => {
            $.ajax(url, {
                method: 'GET'
            }).done(data => {
                const newProps = {
                    conflicts: data.conflicts,
                    availabilitylist: data.availabilityList,
                    busyslots: data.busySlotsForAvailabilities,
                    maxslots: data.maxSlotsForAvailabilities,
                    slotbuckets: data.slotBuckets,
                }
                const mergedProps = Object.assign({}, this.props, newProps)
                this.setState(() => Object.assign({}, getStateFromProps(mergedProps), {
                    stateChanged: false
                }), () => {
                    resolve(this.state)
                })

            }).fail(() => {
                resolve(null)
            })
        })
    }

    onSaveUpdates() {
        const payload = this.prepareAvailabilityPayload();
        $.ajax({
            url: `${this.props.links.includeurl}/availability/checkdayoff/`,
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: response => {
                if (response.overridesDayOff) {
                    const dialogHtml = buildConfirmDialogHtml(
                        'Öffnungszeit für Feiertag',
                        'Sie sind dabei, eine Öffnungszeit für einen Feiertag zu erstellen. Bitte beachten Sie, dass Feiertage normalerweise für Buchungen gesperrt sind.<br><br>Möchten Sie dennoch fortfahren und Termine für diesen Tag zur Buchung freigeben?',
                        'Fortfahren'
                    );
                    BaseView.loadDialogStatic(
                        dialogHtml,
                        () => this.doSaveUpdates(payload),
                        () => {},
                        { $main: $('body') }
                    );
                    return;
                }
                this.doSaveUpdates(payload);
            },
            error: (err) => {
                let isException = err.responseText?.toLowerCase().includes('exception');
                if (err.status >= 400 && isException) {
                    new ExceptionHandler($('.opened'), {
                        code: err.status,
                        message: err.responseText
                    });
                }
            }
        });
    }

    doSaveUpdates(payload) {
        const dialogHtml = buildConfirmDialogHtml(
            'Öffnungszeiten speichern',
            'Möchten Sie wirklich die Änderungen aller Öffnungszeiten speichern?',
            'Speichern'
        );
        BaseView.loadDialogStatic(
            dialogHtml,
            () => {
                showSpinner();

                $.ajax(`${this.props.links.includeurl}/availability/`, {
                    method: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json'
                }).done((success) => {
                    this.refreshData()
                        .then(() => this.getConflictList({ scrollToErrors: false }))
                        .then((conflictList) => {
                            const hasConflicts = (conflictList?.conflictIdList?.length || 0) > 0;

                            const firstConflictedAvailability = hasConflicts
                                ? this.findFirstConflictedAvailability(
                                    conflictList,
                                    this.state.availabilitylist
                                )
                                : null;

                            this.setState({
                                errorList: [],
                                selectedAvailability: firstConflictedAvailability,
                                lastSave: new Date().getTime(),
                                saveSuccess: true,
                                saveType: 'save',
                                saveHasConflicts: hasConflicts,
                                saveConflictCheckFailed: false
                            }, () => {
                                this.scrollToSuccessMessage();
                                hideSpinner();
                            });
                        })
                        .catch(() => {
                            this.setState({
                                errorList: [],
                                selectedAvailability: null,
                                lastSave: new Date().getTime(),
                                saveSuccess: true,
                                saveType: 'save',
                                saveHasConflicts: false,
                                saveConflictCheckFailed: true
                            }, () => {
                                this.scrollToSuccessMessage();
                                hideSpinner();
                            });
                        });
                }).fail((err) => {
                    let isException = err.responseText?.toLowerCase().includes('exception');
                    if (err.status >= 400 && isException) {
                        new ExceptionHandler($('.opened'), {
                            code: err.status,
                            message: err.responseText
                        });
                    }
                    this.updateSaveBarState('save', false);
                    hideSpinner();
                });
            },
            () => {},
            { $main: $('body') }
        );
    }

    prepareAvailabilityPayload() {
    const selectedDate = formatTimestampDate(this.props.timestamp);

    const defaultStartInDays = this.props.scope?.preferences?.appointment?.startInDaysDefault ?? 0;
    const defaultEndInDays   = this.props.scope?.preferences?.appointment?.endInDaysDefault   ?? 60;

    const modifiedAvailabilities = this.state.availabilitylist.filter(availability => {
        const isModified = availability.__modified === true;
        const isTemporary = availability.tempId?.includes('__temp__');

        // Keep conflicted/error availabilities in payload so backend can respond
        // with detailed validation results instead of receiving an empty list.
        return isModified || isTemporary;
    });

    const availabilityPayload = modifiedAvailabilities.map(availability => {

        const availabilityForBackend = { ...availability };
        delete availabilityForBackend.tempId;
        availabilityForBackend.bookable.startInDays =
            availabilityForBackend.bookable.startInDays ?? defaultStartInDays;

        availabilityForBackend.bookable.endInDays =
            availabilityForBackend.bookable.endInDays ?? defaultEndInDays;

        availabilityForBackend.kind = availability.kind || 'default';

        return cleanupAvailabilityForSave(availabilityForBackend);
    });

    return {
        availabilityList: availabilityPayload,
        selectedDate
    };
}

    updateSaveBarState(type, success) {

        this.setState({
            lastSave: new Date().getTime(),
            saveSuccess: success,
            saveType: type,
            saveHasConflicts: false,
            saveConflictCheckFailed: false
        });
    }

    scrollToSuccessMessage() {
        if (!this.successElement) {
            return;
        }

        window.requestAnimationFrame(() => {
            this.successElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    }

    onRevertUpdates() {
        this.isCreatingExclusion = false
        this.setState((prevState, props) => Object.assign({}, getInitialState(props), {
            selectedTab: prevState.selectedTab,
            errorList: []
        }), () => {
            this.refreshData()
        })
    }

    onDeleteAvailability(availability) {
        const id = availability.id;
        const dialogHtml = buildConfirmDialogHtml(
            'Öffnungszeit löschen',
            'Soll diese Öffnungszeit wirklich gelöscht werden?',
            'Löschen'
        );
        BaseView.loadDialogStatic(
            dialogHtml,
            () => {
                showSpinner();
                $.ajax(`${this.props.links.includeurl}/availability/delete/${id}/`, {
                    method: 'DELETE'
                }).done(() => {
                    this.setState((prevState) => {
                        const newState = deleteAvailabilityInState(prevState, availability);

                        if (prevState.fullAvailabilityList) {
                            newState.fullAvailabilityList = prevState.fullAvailabilityList.filter(
                                item => item.id !== availability.id
                            );
                        }

                        if (prevState.selectedAvailability && prevState.selectedAvailability.id === id) {
                            newState.selectedAvailability = null;
                        }

                        return newState
                    }, () => {
                        this.refreshData();
                        this.setState({
                            conflictList: { itemList: {}, conflictIdList: [] }
                        });
                    });

                    this.updateSaveBarState('delete', true);

                    if (this.successElement) {
                        this.successElement.scrollIntoView();
                    }
                    hideSpinner();
                }).fail(err => {
                    const responseText = err.responseText || '';
                    let isException = responseText.toLowerCase().includes('exception');
                    if (err.status >= 400 && isException) {
                        new ExceptionHandler($('.opened'), {
                            code: err.status,
                            message: responseText
                        });
                    }
                    this.updateSaveBarState('delete', false);
                    hideSpinner();
                });
            },
            () => {},
            { $main: $('body') }
        );
    }

    onCopyAvailability(availability) {
        const start = formatTimestampDate(availability.startDate)
        const end = formatTimestampDate(availability.endDate)

        let copysourcetitle = (availability.description) ? availability.description : `${start} - ${end}`;
        const copyAvailability = Object.assign({}, availability, {
            tempId: tempId(),
            id: null,
            description: `Kopie von ${copysourcetitle}`
        })
        this.setState((prevState) => Object.assign(
            {},
            mergeAvailabilityListIntoState(prevState, [copyAvailability]),
            { selectedAvailability: copyAvailability, stateChanged: true }
        ));
    }

    onSelectAvailability(availability) {
        this.setState({
            selectedAvailability: availability || null
        });
    }

    editExclusionAvailability(availability, startDate, endDate, description, kind) {
        (startDate) ? availability.startDate = startDate : null;
        (endDate) ? availability.endDate = endDate : null;
        availability.__modified = true;
        if (!availability.kind && kind != 'origin') {
            availability.tempId = tempId()
            availability.id = null
            availability.description = (description) ? description : availability.description
            availability.kind = kind
        } else {
            availability.kind = 'origin'
        }
        return availability;
    }

    onCreateExclusionForAvailability(availability) {
        const selectedDay = moment(this.props.timestamp, 'X').startOf('day')
        const yesterday = selectedDay.clone().subtract(1, 'days')
        const tomorrow = selectedDay.clone().add(1, 'days')

        this.isCreatingExclusion = true;

        let endDateTimestamp = (parseInt(yesterday.unix(), 10) < availability.startDate) ?
            parseInt(selectedDay.unix(), 10) :
            parseInt(yesterday.unix(), 10);

        let name = availability.description;
        name = name.replaceAll('Ausnahme zu Terminserie ', '');
        name = name.replaceAll('Fortführung der Terminserie ', '');

        const originAvailability = this.editExclusionAvailability(
            Object.assign({}, availability),
            null,
            endDateTimestamp,
            null,
            'origin'
        )

        if (availability.startDate === selectedDay.unix()) {
            originAvailability.description = `Ausnahme zu Terminserie ` + name;
        }

        let exclusionAvailability = originAvailability;
        if (originAvailability.startDate < selectedDay.unix()) {
            exclusionAvailability = this.editExclusionAvailability(
                Object.assign({}, availability),
                parseInt(selectedDay.unix(), 10),
                parseInt(selectedDay.unix(), 10),
                `Ausnahme zu Terminserie ` + name,
                'exclusion'
            )
        }

        let futureAvailability = originAvailability;
        if (parseInt(tomorrow.unix(), 10) <= availability.endDate) {
            futureAvailability = this.editExclusionAvailability(
                Object.assign({}, availability),
                parseInt(tomorrow.unix(), 10),
                null,
                `Fortführung der Terminserie ` + name,
                'future'
            )
        }

        const newAvailabilities = [
            originAvailability,
            exclusionAvailability,
            futureAvailability
        ];

        // Update weekdays for each availability using the same algorithm as validateWeekdays
        const weekdayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        // First, get the original selected weekdays
        const originalSelectedWeekdays = new Set();
        weekdayNames.forEach(day => {
            if (parseInt(availability.weekday[day] || '0') > 0) {
                originalSelectedWeekdays.add(day);
            }
        });

        newAvailabilities.forEach(availability => {
            if (availability.startDate && availability.endDate) {
                const startDate = moment.unix(availability.startDate);
                const endDate = moment.unix(availability.endDate);
                const currentDate = startDate.clone();
                const validWeekdays = new Set();

                // Find all weekdays that occur in this availability's range
                while (currentDate <= endDate) {
                    const dayIndex = currentDate.day();
                    const weekDayName = weekdayNames[dayIndex];
                    validWeekdays.add(weekDayName);
                    currentDate.add(1, 'day');
                }

                // Create a new weekday object for this availability
                const newWeekday = {};
                weekdayNames.forEach(day => {
                    const wasSelected = originalSelectedWeekdays.has(day);
                    const isValidForRange = validWeekdays.has(day);
                    newWeekday[day] = (wasSelected && isValidForRange) ? '1' : '0';
                });

                // Replace the entire weekday object
                availability.weekday = newWeekday;

            }
        });

        this.setState((prevState) => Object.assign({},
            mergeAvailabilityListIntoState(prevState, newAvailabilities),
            {
                selectedAvailability: exclusionAvailability,
                stateChanged: true
            }
        ), () => {
            this.setState({
                conflictList: { itemList: {}, conflictIdList: [] }
            });
            this.isCreatingExclusion = false;
        });
    }

    onEditAvailabilityInFuture(availability) {
        const selectedDay = moment(this.props.timestamp, 'X').startOf('day')
        const yesterday = selectedDay.clone().subtract(1, 'days')
        let endDateTimestamp = (parseInt(yesterday.unix(), 10) < availability.startDate) ?
            parseInt(selectedDay.unix(), 10) :
            parseInt(yesterday.unix(), 10);

        const originAvailability = this.editExclusionAvailability(
            Object.assign({}, availability),
            null,
            endDateTimestamp,
            null,
            'origin'
        )

        let futureAvailability = originAvailability;
        if (parseInt(selectedDay.unix(), 10) <= availability.endDate) {
            futureAvailability = this.editExclusionAvailability(
                Object.assign({}, availability),
                parseInt(selectedDay.unix(), 10),
                null,
                `Änderung ab ${formatTimestampDate(selectedDay)} (${availability.id})`,
                'future'
            )
        }

        this.setState((prevState) => Object.assign({},
            mergeAvailabilityListIntoState(prevState, [
                originAvailability,
                futureAvailability
            ]),
            {
                selectedAvailability: futureAvailability,
                stateChanged: true
            }
        ), () => {
            this.setState({
                conflictList: { itemList: {}, conflictIdList: [] }
            });
        })
    }

    onNewAvailability() {
        const newAvailability = getNewAvailability(this.props.timestamp, tempId(), this.props.scope)
        newAvailability.type = "appointment"
        newAvailability.__modified = true
        this.setState((prevState) => Object.assign(
            {},
            updateAvailabilityInState(prevState, newAvailability),
            {
                selectedAvailability: newAvailability,
                stateChanged: true,
                errorList: [],
                conflictList: { itemList: {}, conflictIdList: [] }
            }
        ), () => {
            $('body').scrollTop(0);
        });
    }

    onTabSelect(tab) {
        this.setState({ selectedTab: tab.component });
    }

    onConflictedIdSelect(id) {
        const availability = this.state.availabilitylist.filter(availability => availability.id === id)[0]
        if (availability) {
            this.setState({ selectedAvailability: availability })
        }
    }

    hasErrors(availability) {
        let hasError = false;
        let hasConflict = false;

        if (this.state.errorList) {
            Object.values(this.state.errorList).forEach(errorItem => {
                if (availability.id === errorItem.id)
                    hasError = true;
            });
        }

        if (this.state.conflictList && this.state.conflictList.conflictIdList) {
            this.state.conflictList.conflictIdList.forEach(id => {
                if (availability.id === id)
                    hasConflict = true;
            });
        }

        return hasError || hasConflict;
    }

    getSortedAvailabilityListForDisplay(availabilityList = this.state.availabilitylist) {
        return [...availabilityList].sort((a, b) => {
            if (a.type === 'appointment' && b.type !== 'appointment') return -1;
            if (a.type !== 'appointment' && b.type === 'appointment') return 1;

            const aTime = a.startTime || '';
            const bTime = b.startTime || '';
            return aTime.localeCompare(bTime);
        });
    }

    findFirstConflictedAvailability(conflictList = this.state.conflictList, availabilityList = this.state.availabilitylist) {
        const conflictIdList = conflictList?.conflictIdList || [];

        if (!conflictIdList.length) {
            return null;
        }

        const normalizedConflictIds = conflictIdList.map(id => String(id));

        return this.getSortedAvailabilityListForDisplay(availabilityList).find(availability => {
            const availabilityId = availability.id ? String(availability.id) : null;
            const availabilityTempId = availability.tempId ? String(availability.tempId) : null;

            return (
                (availabilityId && normalizedConflictIds.includes(availabilityId)) ||
                (availabilityTempId && normalizedConflictIds.includes(availabilityTempId))
            );
        }) || null;
    }

    clearValidationErrorsForAvailability(errorList, availability) {
        const eventId = availability.id || availability.tempId;

        if (!eventId) {
            return Object.values(errorList || {});
        }

        return Object.values(errorList || {}).filter(error => error.id !== eventId);
    }

    getValidationList({ scrollToErrors = false } = {}) {
        return new Promise((resolve, reject) => {
            const validateData = (data) => {
                const validationResult = validate(data, this.props);
                if (!validationResult.valid) {
                    return validationResult.errorList;
                }
                return [];
            };

            try {
                this.setState(
                    (prevState) => {
                        const list = prevState.availabilitylist
                            .map(validateData)
                            .flat();

                        return {
                            errorList: list
                        };
                    },
                    () => {
                        const list = this.state.errorList || [];

                        if (
                            scrollToErrors &&
                            hasBlockingErrors(list, this.state.availabilitylist)
                        ) {
                            this.errorElement?.scrollIntoView();
                        }

                        resolve(list);
                    }
                );
            } catch (error) {
                reject(error);
            }
        });
    }

    getConflictList({ scrollToErrors = false } = {}) {
        const requestOptions = {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.assign({}, {
                availabilityList: this.state.availabilitylist,
                selectedDate: formatTimestampDate(this.props.timestamp),
                selectedAvailability: this.state.selectedAvailability
            }))
        };

        const url = `${this.props.links.includeurl}/availability/conflicts/`;

        return fetch(url, requestOptions)
            .then(async (res) => {
                const responseText = await res.text();
                let data = {};

                try {
                    data = responseText ? JSON.parse(responseText) : {};
                } catch (parseError) {
                    const error = new Error(responseText || res.statusText || 'Invalid JSON response');
                    error.status = res.status;
                    error.responseText = responseText;
                    error.originalError = parseError;
                    throw error;
                }

                if (!res.ok) {
                    const error = new Error(data.message || responseText || res.statusText);
                    error.status = res.status;
                    error.responseText = responseText;
                    error.data = data;
                    throw error;
                }

                return data;
            })
            .then((data) => {
                const conflictList = {
                    itemList: Object.assign({}, data.conflictList),
                    conflictIdList: data.conflictIdList || []
                };

                return new Promise((resolve) => {
                    this.setState({ conflictList }, () => {
                        if (
                            scrollToErrors &&
                            conflictList.conflictIdList.length > 0 &&
                            this.errorElement
                        ) {
                            this.errorElement.scrollIntoView();
                        }

                        resolve(conflictList);
                    });
                });
            })
            .catch((err) => {
                const responseText = err.responseText || '';
                const isException = responseText.toLowerCase().includes('exception');

                if (err.status >= 400 && isException) {
                    new ExceptionHandler($('.opened'), {
                        code: err.status,
                        message: responseText
                    });
                }

                hideSpinner();

                throw err;
            });
    }

    renderTimeTable() {
        const onSelect = data => {
            this.onSelectAvailability(data)
        }

        const onDelete = data => {
            this.onDeleteAvailability(data)
        }

        const ViewComponent = this.state.selectedTab == 'graph' ? GraphView : TableView;
        return <ViewComponent
            timestamp={this.props.timestamp}
            conflicts={this.state.conflicts}
            availabilityList={this.state.availabilitylistslices || this.state.availabilitylist}
            data={this.state.selectedAvailability}
            maxWorkstationCount={this.state.maxWorkstationCount || this.props.maxworkstationcount}
            links={this.props.links}
            onSelect={onSelect}
            onDelete={onDelete}
            onAbort={this.onRevertUpdates.bind(this)}
            slotBuckets={this.state.slotbuckets}
        />
    }

    readCalculatedAvailabilityList() {
        $.ajax(`${this.props.links.includeurl}/availability/slots/`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            data: JSON.stringify({
                'availabilityList': this.state.availabilitylist,
                'busySlots': this.state.busyslots
            })
        }).done((responseData) => {
            this.setState((prevState) => {
                const availabilityList = writeSlotCalculationIntoAvailability(
                    prevState.availabilitylist,
                    responseData['maxSlots'],
                    responseData['busySlots']
                );
                return {
                    availabilitylistslices: availabilityList,
                    maxWorkstationCount: parseInt(responseData['maxWorkstationCount'], 10),
                }
            })
        }).fail((err) => {
            if (err.status === 404) {
                console.log('404 error, ignored')
            } else {
                const responseText = err.responseText || '';
                let isException = responseText.toLowerCase().includes('exception');
                if (err.status >= 400 && isException) {
                    new ExceptionHandler($('.opened'), {
                        code: err.status,
                        message: responseText
                    });
                } else {
                    console.log('reading calculated availability list error', err);
                }
                hideSpinner();
            }
        })
    }

    handleChange(data) {
        if (data.__modified) {
            clearTimeout(this.timer)
            this.setState(
                (prevState) => Object.assign(
                    {},
                    updateAvailabilityInState(prevState, data),
                    {
                        errorList: this.clearValidationErrorsForAvailability(prevState.errorList, data),
                        conflictList: { itemList: {}, conflictIdList: [] }
                    }
                ),
                () => {
                    this.readCalculatedAvailabilityList();
                }
            );
        }
        if (data.kind && inArray(data.kind, ["origin", "future", "exclusion"])) {
            this.handleChangesAvailabilityExclusion(data)
        }
    }

    handleChangesAvailabilityExclusion(data) {
        if ('origin' == data.kind && data.__modified) {
            this.handleOriginChanges(data)
        }
        if ('exclusion' == data.kind && data.__modified) {
            this.handleExclusionChanges(data)
        }
        if ('future' == data.kind && data.__modified) {
            this.handleFutureChanges(data)
        }
    }

    handleOriginChanges(data) {
        const exclusionAvailabilityFromState = findAvailabilityInStateByKind(this.state, 'exclusion');
        const futureAvailabilityFromState = findAvailabilityInStateByKind(this.state, 'future');

        const exclusionAvailability = (exclusionAvailabilityFromState) ? Object.assign({}, exclusionAvailabilityFromState, {
            startDate: moment(data.endDate, 'X').startOf('day').add(1, 'days').unix(),
            endDate: (exclusionAvailabilityFromState.endDate > moment(data.endDate, 'X').startOf('day').add(1, 'days').unix()) ?
                exclusionAvailabilityFromState.endDate :
                moment(data.endDate, 'X').startOf('day').add(1, 'days').unix()
        }) : data;

        const futureAvailability = Object.assign({}, futureAvailabilityFromState, {
            startDate: moment(exclusionAvailability.endDate, 'X').startOf('day').add(1, 'days').unix(),
            endDate: (
                futureAvailabilityFromState.endDate > moment(exclusionAvailability.endDate, 'X').startOf('day').add(1, 'days').unix()) ?
                futureAvailabilityFromState.endDate :
                moment(exclusionAvailability.endDate, 'X').startOf('day').add(1, 'days').unix()
        });
        this.setState((prevState) => Object.assign(
            {},
            mergeAvailabilityListIntoState(prevState, [exclusionAvailability, futureAvailability, data])
        ));
    }

    handleExclusionChanges(data) {
        const originAvailabilityFromState = findAvailabilityInStateByKind(this.state, 'origin');
        const futureAvailabilityFromState = findAvailabilityInStateByKind(this.state, 'future');

        const exclusionAvailability = Object.assign({}, data, {
            endDate: (data.startDate > data.endDate) ? data.startDate : data.endDate
        });

        const originAvailability = Object.assign({}, originAvailabilityFromState, {
            endDate: moment(exclusionAvailability.startDate, 'X').startOf('day').subtract(1, 'days').unix()
        });

        const futureAvailability = Object.assign({}, futureAvailabilityFromState, {
            startDate: moment(exclusionAvailability.endDate, 'X').startOf('day').add(1, 'days').unix(),
            endDate: (
                futureAvailabilityFromState.endDate > moment(data.endDate, 'X').startOf('day').add(1, 'days').unix()) ?
                futureAvailabilityFromState.endDate :
                moment(data.endDate, 'X').startOf('day').add(1, 'days').unix()
        });

        this.setState((prevState) => Object.assign(
            {},
            mergeAvailabilityListIntoState(prevState, [
                originAvailability,
                futureAvailability,
                exclusionAvailability
            ])
        ));
    }

    handleFutureChanges(data) {
        const startDate = moment(data.startDate, 'X').startOf('day').add(1, 'days').unix();
        const originAvailabilityFromState = findAvailabilityInStateByKind(this.state, 'origin');
        const exclusionAvailabilityFromState = findAvailabilityInStateByKind(this.state, 'exclusion');

        const exclusionAvailability = (exclusionAvailabilityFromState) ? Object.assign({}, exclusionAvailabilityFromState, {
            startDate: (startDate < exclusionAvailabilityFromState.endDate) ?
                parseInt(startDate, 10) :
                exclusionAvailabilityFromState.endDate
        }) : data;

        const originAvailability = Object.assign({}, originAvailabilityFromState, {
            endDate: moment(exclusionAvailability.startDate, 'X').startOf('day').subtract(1, 'days').unix()
        });

        this.setState((prevState) => Object.assign(
            {},
            mergeAvailabilityListIntoState(prevState, [originAvailability, exclusionAvailability, data])
        ));
    }

    renderAvailabilityAccordion() {
        const onSelect = data => {
            this.onSelectAvailability(data)
        }
        const onCopy = data => {
            this.onCopyAvailability(data)
        }

        const onExclusion = data => {
            this.onCreateExclusionForAvailability(data)
        }

        const onEditInFuture = data => {
            this.onEditAvailabilityInFuture(data)
        }

        const onDelete = data => {
            this.onDeleteAvailability(data)
        }

        const onNew = data => {
            this.onNewAvailability(data)
        }

        const handleChange = (data) => {
            this.handleChange(data)
        }

        return <AccordionLayout
            availabilityList={this.state.availabilitylist}
            data={this.state.selectedAvailability}
            today={this.state.today}
            timestamp={this.props.timestamp}
            title=""
            onSelect={onSelect}
            onPublish={this.onPublishAvailability.bind(this)}
            onUpdateSingle={this.onPublishAvailability.bind(this)}
            onDelete={onDelete}
            onNew={onNew}
            onAbort={this.onRevertUpdates.bind(this)}
            onCopy={onCopy}
            onExclusion={onExclusion}
            onEditInFuture={onEditInFuture}
            handleChange={handleChange}
            stateChanged={this.state.stateChanged}
            includeUrl={this.props.links.includeurl}
            setErrorRef={this.setErrorRef}
            errorList={this.state.errorList ?
                this.state.errorList : {}
            }
            conflictList={this.state.conflictList ?
                this.state.conflictList :
                { itemList: {}, conflictIdList: [] }
            }
            isCreatingExclusion={this.isCreatingExclusion}
        />
    }

    renderSaveBar({ setSuccessRef = false } = {}) {
        if (this.state.lastSave) {
            return (
                <SaveBar
                    lastSave={this.state.lastSave}
                    success={this.state.saveSuccess}
                    hasConflicts={this.state.saveHasConflicts}
                    conflictCheckFailed={this.state.saveConflictCheckFailed}
                    setSuccessRef={setSuccessRef ? this.setSuccessRef : null}
                    type={this.state.saveType}
                />
            )
        }
    }

    render() {
        return (
            <PageLayout
                tabs={<TabsBar selected={this.state.selectedTab} tabs={this.props.tabs} onSelect={this.onTabSelect.bind(this)} />}
                timeTable={this.renderTimeTable()}
                saveBarTop={this.renderSaveBar({ setSuccessRef: true })}
                accordion={this.renderAvailabilityAccordion()}
                saveBarBottom={this.renderSaveBar({ setSuccessRef: false })}
                conflicts={<Conflicts conflicts={this.state.conflicts} onSelect={this.onConflictedIdSelect.bind(this)} />}
            />
        )
    }
}

AvailabilityPage.propTypes = {
    maxworkstationcount: PropTypes.number,
    timestamp: PropTypes.number,
    scope: PropTypes.object,
    links: PropTypes.object,
    tabs: PropTypes.array
}

export default AvailabilityPage

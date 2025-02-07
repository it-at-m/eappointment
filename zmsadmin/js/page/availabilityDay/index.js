import React, { Component } from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import moment from 'moment'
import validate from './form/validate'
import Conflicts from './conflicts'
import TabsBar from './tabsbar'
import GraphView from './timetable/graphview.js'
import TableView from './timetable/tableview.js'
import ScopeView from './timetable/scopeview.js'
import SaveBar from './saveBar'
import AccordionLayout from './layouts/accordion'
import PageLayout from './layouts/page'
import { inArray, showSpinner, hideSpinner } from '../../lib/utils'
import ExceptionHandler from '../../lib/exceptionHandler';

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
        this.state = {
            ...getInitialState(props),
            fullAvailabilityList: null
        }
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

    getAllScopeAvailabilities() {
        const startDate = moment().startOf('year').format('YYYY-MM-DD')
        const endDate = moment().add(1, 'year').format('YYYY-MM-DD')
        
        const url = `${this.props.links.includeurl}/scope/${this.props.scope.id}/availability/?startDate=${startDate}&endDate=${endDate}`
        
        $.ajax(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(response => {
            const availabilityList = response.data ? Object.values(response.data) : [];
            
            this.setState({
                fullAvailabilityList: availabilityList
            }, () => {
                if (availabilityList.length > 0) {
                    this.getValidationList();
                }
            });
        }).fail(err => {
            console.error('getAllScopeAvailabilities error', err);
        })
    }

    handleScrollToBottom() {
        window.scrollTo(0, document.body.scrollHeight);
    }

    componentDidMount() {
        this.getValidationList()
        this.unloadHandler = ev => {
            const confirmMessage = "Es wurden nicht alle Änderungen gespeichert. Diese gehen beim schließen verloren."
            if (this.state.stateChanged) {
                ev.returnValue = confirmMessage
                return confirmMessage
            }
        }

        window.addEventListener('beforeunload', this.unloadHandler)
    }

    componentDidUnMount() {
        window.removeEventListener('beforeunload', this.unloadHandler)
    }

    onPublishAvailability() {
        this.getValidationList();
        this.getConflictList();
        let state = {};
        state = { selectedAvailability: null }
        this.setState(state, () => {
            this.onSaveUpdates();
        });
    }

    updateDataState(newProps) {
        this.setState({
            conflicts: newProps.conflicts,
            availabilitylist: newProps.availabilityList,
            busyslots: newProps.busySlotsForAvailabilities,
            maxslots: newProps.maxSlotsForAvailabilities,
            slotbuckets: newProps.slotBuckets,
            availabilitylistslices: writeSlotCalculationIntoAvailability(
                newProps.availabilityList,
                newProps.maxSlotsForAvailabilities,
                newProps.busySlotsForAvailabilities
            ),
            stateChanged: false
        });
    }

    refreshData() {
        const currentDate = formatTimestampDate(this.props.timestamp)
        const url = `${this.props.links.includeurl}/scope/${this.props.scope.id}/availability/day/${currentDate}/conflicts/`
        $.ajax(url, {
            method: 'GET'
        }).done(data => {
            this.updateDataState(data);
        }).fail(err => {
            console.log('refreshData error', err)
        })
    }

    onSaveUpdates() {
        const ok = confirm('Möchten Sie wirklich die Änderungen aller Öffnungszeiten speichern?');
        if (ok) {
            showSpinner();
            const selectedDate = formatTimestampDate(this.props.timestamp);
            const sendData = this.state.availabilitylist
                .filter((availability) => {
                    return (
                        (availability.__modified ||
                            (availability.tempId && availability.tempId.includes('__temp__'))) &&
                        !this.hasErrors(availability)
                    );
                })
                .map(availability => {
                    const sendAvailability = Object.assign({}, availability);
                    if (availability.tempId) {
                        delete sendAvailability.tempId;
                    }
                    if (sendAvailability.kind === 'new') {
                        sendAvailability.description = 'Öffnungszeit für Terminvergabe';
                    }
                    return {
                        ...sendAvailability,
                        kind: availability.kind || 'default',
                    };
                })
                .map(cleanupAvailabilityForSave);

            const payload = {
                availabilityList: sendData,
                selectedDate: selectedDate
            };

            $.ajax(`${this.props.links.includeurl}/availability/`, {
                method: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json'
            }).done((success) => {
                this.refreshData();
                this.getValidationList();
                this.getAllScopeAvailabilities();
                this.updateSaveBarState('save', true);

                if (this.successElement) {
                    this.successElement.scrollIntoView();
                }
                hideSpinner();
            }).fail((err) => {
                let isException = err.responseText.toLowerCase().includes('exception');
                if (err.status >= 500 && isException) {
                    new ExceptionHandler($('.opened'), {
                        code: err.status,
                        message: err.responseText
                    });
                } else {
                    console.log('save all error', err);
                }
                this.updateSaveBarState('save', false);
                this.getValidationList();
                hideSpinner();
            });
        } else {
            hideSpinner();
        }
    }

    onRevertUpdates() {
        this.isCreatingExclusion = false;
        this.setState({
            errorList: {},
            conflictList: {
                itemList: {},
                conflictIdList: []
            }
        }, () => {
            this.refreshData(() => {
                this.getValidationList();
                if (this.state.availabilitylist.length > 0) {
                    this.getConflictList();
                }
            });
        });
    }

    onUpdateSingleAvailability(availability) {
        showSpinner();
        const ok = confirm('Soll diese Öffnungszeit wirklich aktualisiert werden?');
        const id = availability.id;

        if (ok) {
            const selectedDate = formatTimestampDate(this.props.timestamp);
            const sendAvailability = Object.assign({}, availability);

            if (sendAvailability.tempId) {
                delete sendAvailability.tempId;
            }

            const payload = {
                availabilityList: [
                    {
                        ...cleanupAvailabilityForSave(sendAvailability),
                        kind: availability.kind || 'default'
                    }
                ],
                selectedDate: selectedDate
            };

            $.ajax(`${this.props.links.includeurl}/availability/save/${id}/`, {
                method: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json'
            }).done((data) => {
                this.refreshData();
                this.getValidationList();
                this.getAllScopeAvailabilities();
                this.updateSaveBarState('save', true);

                if (this.successElement) {
                    this.successElement.scrollIntoView();
                }
                hideSpinner();
            }).fail(err => {
                const isException = err.responseText.toLowerCase().includes('exception');
                if (isException) {
                    new ExceptionHandler($('.opened'), {
                        code: err.status,
                        message: err.responseText
                    });
                } else {
                    console.log('Update error:', err);
                }
                this.updateSaveBarState('save', false);
                this.getValidationList();
                hideSpinner();
            });
        } else {
            hideSpinner();
        }
    }

    updateSaveBarState(type, success) {
        this.setState({
            lastSave: new Date().getTime(),
            saveSuccess: success,
            saveType: type
        });
    }

    onDeleteAvailability(availability) {
        showSpinner();
        const ok = confirm('Soll diese Öffnungszeit wirklich gelöscht werden?')
        const id = availability.id
        if (ok) {
            $.ajax(`${this.props.links.includeurl}/availability/delete/${id}/`, {
                method: 'GET'
            }).done(() => {
                
                const newState = deleteAvailabilityInState(this.state, availability);

                if (this.state.fullAvailabilityList) {
                    newState.fullAvailabilityList = this.state.fullAvailabilityList.filter(
                        item => item.id !== availability.id
                    );
                }

                this.refreshData();
                if (newState.availabilitylist.length > 0) {
                    this.getConflictList();
                }
                this.getValidationList();

                this.getAllScopeAvailabilities();

                this.updateSaveBarState('delete', true);

                if (this.successElement) {
                    this.successElement.scrollIntoView();
                }
                hideSpinner();
            }).fail(err => {
                console.log('delete error', err);
                let isException = err.responseText.toLowerCase().includes('exception');
                if (err.status >= 500 && isException) {
                    new ExceptionHandler($('.opened'), {
                        code: err.status,
                        message: err.responseText
                    });
                } else {
                    console.log('delete error', err);
                }
                this.updateSaveBarState('delete', false);
                hideSpinner();
            })
        } else {
            hideSpinner();
        }
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
        this.setState(Object.assign(
            {},
            mergeAvailabilityListIntoState(this.state, [copyAvailability]),
            { selectedAvailability: copyAvailability, stateChanged: true }
        ), () => {
            this.getValidationList()
        })
    }

    onSelectAvailability(availability) {
        if (availability || !this.state.selectedAvailability) {
            this.setState({
                selectedAvailability: availability
            }, () => {
                this.getValidationList()
            })
        } else {
            this.setState({
                selectedAvailability: null
            })
        }

    }

    editExclusionAvailability(availability, startDate, endDate, description, kind) {
        (startDate) ? availability.startDate = startDate : null;
        (endDate) ? availability.endDate = endDate : null;
        availability.__modified = true;
        availability.description = (description) ? description : availability.description;
        
        if (!availability.kind && kind != 'origin') {
            availability.tempId = tempId()
            availability.id = null
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
        name = name.replaceAll('Ursprüngliche Serie ', '');
    
        const originAvailability = this.editExclusionAvailability(
            Object.assign({}, availability),
            null,
            endDateTimestamp,
            `Ursprüngliche Serie ` + name,
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
    
        this.setState(Object.assign({},
            mergeAvailabilityListIntoState(this.state, [
                originAvailability,
                exclusionAvailability,
                futureAvailability
            ]),
            {
                selectedAvailability: exclusionAvailability,
                stateChanged: true
            }
        ), () => {
            console.log('in after merging', this.state.availabilitylist);
            this.getConflictList(),
                this.getValidationList()
        })
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

        this.setState(Object.assign({},
            mergeAvailabilityListIntoState(this.state, [
                originAvailability,
                futureAvailability
            ]),
            {
                selectedAvailability: futureAvailability,
                stateChanged: true
            }
        ), () => {
            this.getConflictList(),
                this.getValidationList()
        })
    }

    onNewAvailability() {
        let state = {};
        const newAvailability = getNewAvailability(this.props.timestamp, tempId(), this.props.scope, this.state.availabilitylist);
        newAvailability.type = "appointment";

        state = Object.assign(
            state,
            updateAvailabilityInState(this.state, newAvailability)
        );

        state.fullAvailabilityList = [
            ...(this.state.fullAvailabilityList || []),
            newAvailability
        ];

        state.selectedAvailability = newAvailability;
        state.stateChanged = true;

        this.setState(state, () => {
            Promise.all([
                this.getValidationList(),
                this.getConflictList()
            ])
                .then(() => {
                    $('body').scrollTop(0);
                })
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

    validateAvailabilityList(availabilitylist) {
        const timeRegex = /^\d{2}:\d{2}(:\d{2})?$/;

        const isValidTimestamp = (timestamp) => !Number.isNaN(Number(timestamp)) && moment.unix(timestamp).isValid();

        const invalidAvailabilities = availabilitylist.filter((availability) => {
            const hasInvalidDates =
                !isValidTimestamp(availability.startDate) || !isValidTimestamp(availability.endDate);
            const hasInvalidTimes =
                !timeRegex.test(availability.startTime) || !timeRegex.test(availability.endTime);

            if (hasInvalidDates || hasInvalidTimes) {
                console.warn("Invalid availability detected:", availability);
            }

            return hasInvalidDates || hasInvalidTimes;
        });

        return invalidAvailabilities;
    }

    getValidationList() {
        return new Promise((resolve, reject) => {
            const validateData = (data) => {
                const validationResult = validate(data, this.props);
                if (!validationResult.valid) {
                    return validationResult.errorList;
                }
                return [];
            };

            try {
                const list = this.state.availabilitylist
                    .map(validateData)
                    .flat();

                console.log("Validations fetched successfully:", list);

                this.setState(
                    {
                        errorList: list.length ? list : [],
                    },
                    () => {
                        if (list.length > 0) {
                            const nonPastTimeErrors = list.filter(error =>
                                !error.itemList?.flat(2).some(item => item?.type === 'endTimePast')
                            );

                            if (nonPastTimeErrors.length > 0) {
                                console.warn("Validation failed with errors:", nonPastTimeErrors);
                                this.errorElement?.scrollIntoView();
                            } else {
                                console.log("Validation passed with only past time errors.");
                            }
                        } else {
                            console.log("Validation passed successfully.");
                            resolve();
                        }
                    }
                );
            } catch (error) {
                console.error("Validation error:", error);
                reject(error);
            }
        });
    }

    getConflictList() {
        return new Promise((resolve, reject) => {
            const { availabilitylist } = this.state;
            const { timestamp } = this.props;
            const requestOptions = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    availabilityList: availabilitylist,
                    selectedDate: formatTimestampDate(timestamp)
                }),
            };

            const url = `${this.props.links.includeurl}/availability/conflicts/`;

            fetch(url, requestOptions)
                .then((res) => res.json())
                .then(
                    (data) => {
                        console.log("Conflicts fetched successfully:", data);
                        this.setState({
                            conflictList: {
                                itemList: { ...data.conflictList },
                                conflictIdList: data.conflictIdList,
                            },
                        });
                        if (data.conflictIdList.length > 0) {
                            this.errorElement?.scrollIntoView();
                        }
                        resolve();
                    },
                    (err) => {
                        console.error("Conflict fetch error:", err);
                        hideSpinner();
                        reject(err);
                    }
                )
                .catch((error) => {
                    console.warn("Conflict fetch failed:", error);
                    reject(error);
                });
        });
    }

    renderTimeTable() {
        const onSelect = data => {
            this.onSelectAvailability(data)
        }
    
        const onDelete = data => {
            this.onDeleteAvailability(data)
        }
    
        let ViewComponent
        let availabilityList
        switch(this.state.selectedTab) {
            case 'graph':
                ViewComponent = GraphView
                availabilityList = this.state.availabilitylistslices || this.state.availabilitylist
                break
            case 'scope':
                ViewComponent = ScopeView
                availabilityList = this.state.fullAvailabilityList || []
                if (!this.state.fullAvailabilityList) {
                    this.getAllScopeAvailabilities()
                }
                break
            default:
                ViewComponent = TableView
                availabilityList = this.state.availabilitylistslices || this.state.availabilitylist
        }
    
        return <ViewComponent
            timestamp={this.props.timestamp}
            scope={this.props.scope}
            conflicts={this.state.conflicts}
            availabilityList={availabilityList}
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
        this.getValidationList()
            .then(() => {
                const { availabilitylist, busyslots } = this.state;

                console.log("Validation passed. Proceeding with /availability/slots/.");

                $.ajax(`${this.props.links.includeurl}/availability/slots/`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    data: JSON.stringify({
                        availabilityList: availabilitylist,
                        busySlots: busyslots,
                    }),
                })
                    .done((responseData) => {
                        console.log("Slots fetched successfully:", responseData);
                        const availabilityList = writeSlotCalculationIntoAvailability(
                            this.state.availabilitylist,
                            responseData['maxSlots'],
                            responseData['busySlots']
                        );
                        this.setState({
                            availabilitylistslices: availabilityList,
                            maxWorkstationCount: parseInt(responseData['maxWorkstationCount']),
                        });
                    })
                    .fail((err) => {
                        console.error("Error during /availability/slots/ fetch:", err);
                        if (err.status === 404) {
                            console.log("404 error ignored.");
                            hideSpinner();
                        } else {
                            const isException = err.responseText.toLowerCase().includes("exception");
                            if (err.status >= 500 && isException) {
                                new ExceptionHandler($(".opened"), {
                                    code: err.status,
                                    message: err.responseText,
                                });
                            } else {
                                console.error("Unexpected error:", err.responseText);
                            }
                            hideSpinner();
                        }
                    });
            })
            .catch((error) => {
                console.warn("Validation failed. Slot calculation fetch aborted.", error);
                this.setState({ errorList: error });
                this.errorElement?.scrollIntoView();
            });
    }

    handleChange(data) {
        if (data.__modified) {
            clearTimeout(this.timer)
            const state = Object.assign({}, updateAvailabilityInState(this.state, data));
            
            if (this.state.fullAvailabilityList) {
                state.fullAvailabilityList = this.state.fullAvailabilityList.map(item => 
                    (item.id === data.id || item.tempId === data.tempId) ? data : item
                );
                
                if (!state.fullAvailabilityList.some(item => 
                    item.id === data.id || item.tempId === data.tempId
                )) {
                    state.fullAvailabilityList = [...state.fullAvailabilityList, data];
                }
            }
            
            this.setState(state, () => {
                this.readCalculatedAvailabilityList();
                if (data.tempId || data.id) {
                    this.timer = setTimeout(() => {
                        this.getConflictList()
                        this.getValidationList()
                    }, this.waitintervall)
                }
            });
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
        this.setState(Object.assign(
            {},
            mergeAvailabilityListIntoState(this.state, [exclusionAvailability, futureAvailability, data])
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

        this.setState(Object.assign(
            {},
            mergeAvailabilityListIntoState(this.state, [
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

        this.setState(Object.assign(
            {},
            mergeAvailabilityListIntoState(this.state, [originAvailability, exclusionAvailability, data])
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
    
        const onUpdateSingle = data => {
            this.onUpdateSingleAvailability(data)
        }
    
        const onNew = data => {
            this.onNewAvailability(data)
        }
    
        const handleChange = (data) => {
            this.handleChange(data)
        }
    
        const availabilityList = this.state.selectedTab === 'scope' 
            ? (this.state.fullAvailabilityList || [])
            : this.state.availabilitylist
    
        return <AccordionLayout
            availabilityList={availabilityList}
            data={this.state.selectedAvailability}
            today={this.state.today}
            timestamp={this.props.timestamp}
            title=""
            onSelect={onSelect}
            onPublish={this.onPublishAvailability.bind(this)}
            onUpdateSingle={onUpdateSingle}
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
                { itemList: {}, conflictIdList: {} }
            }
            isCreatingExclusion={this.isCreatingExclusion}
        />
    }

    renderSaveBar() {
        if (this.state.lastSave) {
            return (
                <SaveBar
                    lastSave={this.state.lastSave}
                    success={this.state.saveSuccess}
                    setSuccessRef={this.setSuccessRef}
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
                saveBar={this.renderSaveBar()}
                accordion={this.renderAvailabilityAccordion()}
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

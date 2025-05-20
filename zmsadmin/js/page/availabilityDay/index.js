import React, { Component } from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import moment from 'moment'
import validate from './form/validate'
import Conflicts from './conflicts'
import TabsBar from './tabsbar'
import GraphView from './timetable/graphview.js'
import TableView from './timetable/tableview.js'
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

    refreshData() {
        const currentDate = formatTimestampDate(this.props.timestamp)
        const url = `${this.props.links.includeurl}/scope/${this.props.scope.id}/availability/day/${currentDate}/conflicts/`
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
            this.setState(Object.assign({}, getStateFromProps(Object.assign({}, this.props, newProps)), {
                stateChanged: false
            }))

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
                    if (sendAvailability.bookable.startInDays === undefined || sendAvailability.bookable.startInDays === null || sendAvailability.bookable.startInDays === '') {
                        sendAvailability.bookable.startInDays = this.props.scope.preferences.appointment.startInDaysDefault || 0;
                    }
                    if (sendAvailability.bookable.endInDays === undefined || sendAvailability.bookable.endInDays === null || sendAvailability.bookable.endInDays === '') {
                        sendAvailability.bookable.endInDays = this.props.scope.preferences.appointment.endInDaysDefault || 60;
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
                this.updateSaveBarState('save', true);

                if (this.successElement) {
                    this.successElement.scrollIntoView();
                }
                hideSpinner();
            }).fail((err) => {
                let isException = err.responseText.toLowerCase().includes('exception');
                if (err.status >= 400 && isException) {
                    new ExceptionHandler($('.opened'), {
                        code: err.status,
                        message: err.responseText
                    });
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

    onRevertUpdates() {
        this.isCreatingExclusion = false
        this.setState(Object.assign({}, getInitialState(this.props), {
            selectedTab: this.state.selectedTab
        }), () => {
            this.refreshData()
            this.getValidationList()
        })
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

                this.updateSaveBarState('delete', true);

                if (this.successElement) {
                    this.successElement.scrollIntoView();
                }
                hideSpinner();
            }).fail(err => {
                console.log('delete error', err);
                let isException = err.responseText.toLowerCase().includes('exception');
                if (err.status >= 400 && isException) {
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
        const germanWeekdays = {
            'sunday': 'Sonntag',
            'monday': 'Montag',
            'tuesday': 'Dienstag',
            'wednesday': 'Mittwoch',
            'thursday': 'Donnerstag',
            'friday': 'Freitag',
            'saturday': 'Samstag'
        };

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

        this.setState(Object.assign({},
            mergeAvailabilityListIntoState(this.state, newAvailabilities),
            {
                selectedAvailability: exclusionAvailability,
                stateChanged: true
            }
        ), () => {
            this.getConflictList(),
                this.getValidationList()
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
        const newAvailability = getNewAvailability(this.props.timestamp, tempId(), this.props.scope)
        newAvailability.type = "appointment"
        newAvailability.__modified = true
        state = Object.assign(
            state,
            updateAvailabilityInState(this.state, newAvailability),
            { selectedAvailability: null, stateChanged: true }
        );
        this.setState(state);
        $('body').scrollTop(0);
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
                            }
                        } else {
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
        fetch(url, requestOptions)
            .then(res => res.json())
            .then(
                (data) => {
                    this.setState({
                        conflictList: Object.assign({},
                            {
                                itemList: Object.assign({}, data.conflictList),
                                conflictIdList: data.conflictIdList
                            }
                        )
                    })
                    if (data.conflictIdList.length > 0) {
                        this.errorElement.scrollIntoView()
                    }
                },
                (err) => {
                    let isException = err.responseText.toLowerCase().includes('exception');
                    if (err.status >= 400 && isException) {
                        new ExceptionHandler($('.opened'), {
                            code: err.status,
                            message: err.responseText
                        });
                    } else {
                        console.log('conflict error', err);
                    }
                    hideSpinner();
                }
            )
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
            let availabilityList = writeSlotCalculationIntoAvailability(
                this.state.availabilitylist,
                responseData['maxSlots'],
                responseData['busySlots']
            );
            this.setState({
                availabilitylistslices: availabilityList,
                maxWorkstationCount: parseInt(responseData['maxWorkstationCount']),
            })
        }).fail((err) => {
            if (err.status === 404) {
                console.log('404 error, ignored')
            } else {
                let isException = err.responseText.toLowerCase().includes('exception');
                if (err.status >= 400 && isException) {
                    new ExceptionHandler($('.opened'), {
                        code: err.status,
                        message: err.responseText
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
                Object.assign({}, updateAvailabilityInState(this.state, data)),
                () => {
                    this.readCalculatedAvailabilityList();
                    if (data.tempId || data.id) {
                        this.timer = setTimeout(() => {
                            this.getConflictList()
                            this.getValidationList()
                        }, this.waitintervall)
                    }
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

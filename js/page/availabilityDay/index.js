import React, { Component } from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import moment from 'moment'
import Conflicts from './conflicts'
import TabsBar from './tabsbar'
import GraphView from './timetable/graphview.js'
import TableView from './timetable/tableview.js'
import SaveBar from './saveBar'
import AccordionLayout from './layouts/accordion'
import PageLayout from './layouts/page'
import { inArray } from '../../lib/utils'

import {
    getInitialState,
    getStateFromProps,
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

    componentDidUnMount() {
        window.removeEventListener('beforeunload', this.unloadHandler)
    }

    onUpdateAvailability(availability) {
        let state = {};
        if (availability.__modified || this.state.stateChanged) {
            state = Object.assign(state, updateAvailabilityInState(this.state, availability), {
                selectedAvailability: null
            })
        } else {
            state = { selectedAvailability: null }
        }
        this.setState(state);
        $('body').scrollTop(0);
        return state;
    }

    onPublishAvailability(availability) {
        const state = this.onUpdateAvailability(availability);
        this.onSaveUpdates(state);
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
                availabilitylistslices: data.availabilityListSlices,
                busyslots: data.busySlotsForAvailabilities,
                maxslots: data.maxSlotsForAvailabilities
            }

            this.setState(Object.assign({}, getStateFromProps(Object.assign({}, this.props, newProps)), {
                stateChanged: false,
                selectedAvailability: null
            }))
        }).fail(err => {
            console.log('refreshData error', err)
        })
    }

    onSaveUpdates(stateParam) {

        const state = stateParam ? stateParam : this.state
        const sendData = state.availabilitylist.map(availability => {
            const sendAvailability = Object.assign({}, availability)
            if (availability.tempId) {
                delete sendAvailability.tempId
            }

            return sendAvailability
        }).map(cleanupAvailabilityForSave)

        console.log('Saving updates', sendData)

        $.ajax(`${this.props.links.includeurl}/availability/`, {
            method: 'POST',
            data: JSON.stringify(sendData)
        }).done((success) => {
            console.log('save success', success)
            this.setState({
                lastSave: new Date().getTime()
            })
            this.refreshData()
        }).fail((err) => {
            if (err.status === 404) {
                console.log('404 error, ignored')
                this.refreshData()
            } else {
                console.log('save error', err)
            }
        })
    }


    onRevertUpdates() {
        //this.setState(getInitialState(this.props))
        this.setState(Object.assign({}, getInitialState(this.props), {
            selectedTab: this.state.selectedTab
        }), () => {
            this.getConflictList()
        })
    }

    onDeleteAvailability(availability) {
        console.log('Deleting', availability)
        const ok = confirm('Soll diese Öffnungszeit wirklich gelöscht werden?')
        const id = availability.id
        if (ok) {
            $.ajax(`${this.props.links.includeurl}/availability/${id}`, {
                method: 'DELETE'
            }).done(() => {
                this.setState(Object.assign({}, deleteAvailabilityInState(this.state, availability), {
                    selectedAvailability: null
                }), () => {
                    //after removing the deleted entry, sav the updated list again.
                    this.onSaveUpdates()
                })
            }).fail(err => {
                console.log('delete error', err)
            })
        }
    }

    onCopyAvailability(availability) {
        const start = formatTimestampDate(availability.startDate)
        const end = formatTimestampDate(availability.endDate)

        const copyAvailability = Object.assign({}, availability, {
            tempId: tempId(),
            id: null,
            description: `Kopie von "${start} - ${end}"`
        })
        this.setState(Object.assign(
            {},
            mergeAvailabilityListIntoState(this.state, [copyAvailability]),
            { selectedAvailability: copyAvailability, stateChanged: true }
        ), () => {
            this.getConflictList()
        })
    }

    onSelectAvailability(availability) {
        this.setState({
            selectedAvailability: availability
        }, () => {
            this.getConflictList()
        })
    }

    editExclusionAvailability(availability, startDate, endDate, description, kind) {
        (startDate) ? availability.startDate = startDate : null;
        (endDate) ? availability.endDate = endDate : null;
        availability.__modified = true;
        if (! availability.kind && kind != 'origin') {
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

        const exclusionAvailability = this.editExclusionAvailability(
            Object.assign({}, availability),
            parseInt(selectedDay.unix(), 10), 
            parseInt(selectedDay.unix(), 10),
            `Ausnahme ${formatTimestampDate(selectedDay)} (${availability.id})`,
            'exclusion'
        )
        let futureAvailability = null;
        if (parseInt(tomorrow.unix(), 10) < availability.endDate) {
            futureAvailability = this.editExclusionAvailability(
                Object.assign({}, availability),
                parseInt(tomorrow.unix(), 10),
                null,
                `Fortführung der Ausnahme ${formatTimestampDate(selectedDay)} (${availability.id})`,
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
        ))
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

        let futureAvailability = null;
        if (parseInt(selectedDay.unix(), 10) < availability.endDate) {
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
        ))
    }

    onNewAvailability() {
        let state = {};
        const newAvailability = getNewAvailability(this.props.timestamp, tempId(), this.props.scope)
        state = Object.assign(
            state, 
            updateAvailabilityInState(this.state, newAvailability), 
            { selectedAvailability: newAvailability, stateChanged: false }
        );
        this.setState(state, () => {
            this.getConflictList()
        });
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

    getConflictList() {
        const requestOptions = {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.assign({}, {availabilityList: this.state.availabilitylist, selectedAvailability: this.state.selectedAvailability}))
        };
        const url = `${this.props.links.includeurl}/availability/conflicts/`;
        fetch(url, requestOptions)
            .then(res => res.json())
            .then(
                (data) => {
                    this.setState({
                        conflictList: Object.assign({}, data.conflictList)
                    })
                },
                (error) => {
                    console.log(error)
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
            availabilities={this.state.availabilitylist}
            availabilityListSlices={this.state.availabilitylistslices}
            maxWorkstationCount={this.props.maxworkstationcount}
            links={this.props.links}
            onSelect={onSelect}
            onDelete={onDelete}
            onAbort={this.onRevertUpdates.bind(this)}
        />
    }

    handleFocus(element) {
        if (element) {
            element.scrollIntoView()
        }
    }

    handleChange(data) {
        if (data.__modified) {
            this.setState(
                Object.assign({}, updateAvailabilityInState(this.state, data), {selectedAvailability: data}),
                () => {
                    if (data.tempId || data.id) {
                        this.getConflictList()
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
                exclusionAvailabilityFromState.endDate,
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
        const handleErrorList = list => {
            this.setState({
                errorList: list
            })
        }
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

        const onPublish = data => {
            this.onPublishAvailability(data)
        }

        const onNew = data => {
            this.onNewAvailability(data)
        }

        const handleChange = (data) => {
            this.handleChange(data)
        }

        return <AccordionLayout 
            availabilities={this.state.availabilitylist}
            data={this.state.selectedAvailability ? this.state.selectedAvailability : null}
            today={this.state.today}
            timestamp={this.props.timestamp}
            title=""
            onSelect={onSelect}
            onPublish={onPublish}
            onDelete={onDelete}
            onNew={onNew}
            onAbort={this.onRevertUpdates.bind(this)}
            onCopy={onCopy}
            onExclusion={onExclusion}
            onEditInFuture={onEditInFuture}
            handleFocus={this.handleFocus.bind(this)}
            handleChange={handleChange}
            stateChanged={this.state.stateChanged}
            includeUrl={this.props.links.includeurl}
            errorList={this.state.errorList}
            conflictList={this.state.conflictList ? this.state.conflictList : {}}
            handleErrorList={handleErrorList}
        />
    }

    renderSaveBar() {
        if (this.state.lastSave) {
            return <SaveBar lastSave={this.state.lastSave} />
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

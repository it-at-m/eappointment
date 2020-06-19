

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import moment from 'moment'
import AvailabilityForm from './form'
import Conflicts from './conflicts'
import TimeTable from './timetable'
import UpdateBar from './updateBar'
import SaveBar from './saveBar'
import validate from './form/validate'

import PageLayout from './layouts/page'

import {
    getInitialState,
    getStateFromProps,
    getNewAvailability,
    mergeAvailabilityListIntoState,
    updateAvailabilityInState,
    cleanupAvailabilityForSave,
    deleteAvailabilityInState
} from "./helpers"

const tempId = (() => {
    let lastId = -1

    return () => {
        lastId += 1
        return `__temp__${lastId}`
    }
})()

const formatTimestampDate = timestamp => moment(timestamp, 'X').format('YYYY-MM-DD')

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

    componentWillUnMount() {
        window.removeEventListener('beforeunload', this.unloadHandler)
    }

    onUpdateAvailability(availability) {
        let state = {};
        if (availability.__modified) {
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
            return this.withExceptionIfExist(state, availability)
        }).map(cleanupAvailabilityForSave).filter(Boolean)

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

    withExceptionIfExist(state, availability){
        const sendAvailability = Object.assign({}, availability)
        if (availability.fromException) { 
            const dependingAvailability = state.availabilitylist.find(item => item.__modified === true) 
            const selectedStartDate = moment(dependingAvailability.startDate, 'X').startOf('day');
            const selectedEndDate = moment(dependingAvailability.endDate, 'X').startOf('day');
            if ('past' == sendAvailability.fromException) {
                sendAvailability.endDate = selectedStartDate.clone().subtract(1, 'days').unix();
            }
            if ('future' == sendAvailability.fromException) {
                sendAvailability.startDate = selectedEndDate.clone().add(1, 'days').unix();
            }
        }
        return sendAvailability;
    }


    onRevertUpdates() {
        this.setState(getInitialState(this.props))
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
        this.setState({
            selectedAvailability: Object.assign({}, availability, {
                tempId: tempId(),
                id: null,
                description: `Kopie von "${start} - ${end}"`
            }),
            formTitle: "Öffnungszeit kopieren"
        })
    }

    onCreateExceptionForAvailability(availability) {
        const validationResult = validate(availability, this.props)
        if (false === validationResult.valid) {
            this.setState({ errors: validationResult.errors })
            this.handleFocus(this.errorElement);
        }
        const selectedDay = moment(this.props.timestamp, 'X').startOf('day')

        const pastAvailability = Object.assign({}, availability, {
            fromException: 'past'
        })

        const exceptionAvailability = Object.assign({}, availability, {
            startDate: parseInt(selectedDay.format('X'), 10),
            endDate: parseInt(selectedDay.format('X'), 10),
            tempId: tempId(),
            id: null,
            description: `Ausnahme für ${availability.id}`
        })

        const futureAvailability = Object.assign({}, availability, {
            tempId: tempId(),
            id: null,
            fromException: 'future'
        })

        this.setState(Object.assign(
            {},
            mergeAvailabilityListIntoState(this.state, [
                pastAvailability,
                exceptionAvailability,
                futureAvailability
            ]),
            { selectedAvailability: exceptionAvailability, formTitle: "Ausnahme-Öffnungszeit" }
        ))

    }

    onEditAvailabilityInFuture(availability) {
        const validationResult = validate(availability, this.props)
        if (false === validationResult.valid) {
            this.setState({ errors: validationResult.errors })
            this.handleFocus(this.errorElement);
        }

        const selectedDay = moment(this.props.timestamp, 'X').startOf('day')
        const yesterday = selectedDay.clone().subtract(1, 'days')

        const pastAvailability = Object.assign({}, availability, {
            endDate: parseInt(yesterday.format('X'), 10)
        })

        const futureAvailability = Object.assign({}, availability, {
            startDate: parseInt(selectedDay.format('X'), 10),
            tempId: tempId(),
            id: null,
            description: `Änderung ab ${formatTimestampDate(this.props.timestamp)}`
        })

        this.setState(Object.assign(
            {},
            mergeAvailabilityListIntoState(this.state, [
                pastAvailability,
                futureAvailability
            ]),
            { selectedAvailability: futureAvailability, formTitle: "Neue Öffnungszeit ab Datum" }
        ))
    }

    onNewAvailability() {
        console.log('new availability')
        const newAvailability = getNewAvailability(this.props.timestamp, tempId(), this.props.scope)

        this.setState(Object.assign({}, {
            selectedAvailability: newAvailability,
            formTitle: "Neue Öffnungszeit"
        }))
    }

    onConflictedIdSelect(id) {
        const availability = this.state.availabilitylist.filter(availability => availability.id === id)[0]

        if (availability) {
            this.setState({ selectedAvailability: availability })
        }
    }

    renderTimeTable() {
        const onSelect = data => {
            this.setState({
                selectedAvailability: data,
                formTitle: null
            })
        }

        const selectedDaysAvailabilities = this.state.availabilitylist.filter(availability => {
            const start = moment(availability.startDate, 'X')
            const end = moment(availability.endDate, 'X')
            const selectedDay = moment(this.props.timestamp, 'X').startOf('day')

            return start.isSameOrBefore(selectedDay) && end.isSameOrAfter(selectedDay)
        })


        return <TimeTable
            timestamp={this.props.timestamp}
            conflicts={this.state.conflicts}
            availabilities={selectedDaysAvailabilities}
            availabilityListSlices={this.state.availabilitylistslices}
            maxWorkstationCount={this.props.maxworkstationcount}
            links={this.props.links}
            onSelect={onSelect}
            onNewAvailability={this.onNewAvailability.bind(this)}
        />
    }

    handleFocus(element) {
        if (element) {
            element.scrollIntoView()
        }
    }

    renderForm() {
        if (this.state.selectedAvailability) {
            return <AvailabilityForm data={this.state.selectedAvailability}
                today={this.state.today}
                timestamp={this.props.timestamp}
                title={this.state.formTitle}
                onSave={this.onUpdateAvailability.bind(this)}
                onPublish={this.onPublishAvailability.bind(this)}
                onDelete={this.onDeleteAvailability.bind(this)}
                onAbort={this.onRevertUpdates.bind(this)}
                onCopy={this.onCopyAvailability.bind(this)}
                onException={this.onCreateExceptionForAvailability.bind(this)}
                onEditInFuture={this.onEditAvailabilityInFuture.bind(this)}
                handleFocus={this.handleFocus.bind(this)}
            />
        }
    }

    renderUpdateBar() {
        if (this.state.stateChanged && !this.state.selectedAvailability) {
            return <UpdateBar onSave={this.onSaveUpdates.bind(this)} onRevert={this.onRevertUpdates.bind(this)} />
        }
    }

    renderSaveBar() {
        if (this.state.lastSave) {
            return <SaveBar lastSave={this.state.lastSave} />
        }
    }

    render() {
        return (
            <PageLayout
                timeTable={this.renderTimeTable()}
                updateBar={this.renderUpdateBar()}
                saveBar={this.renderSaveBar()}
                form={this.renderForm()}
                conflicts={<Conflicts conflicts={this.state.conflicts} onSelect={this.onConflictedIdSelect.bind(this)} />}
            />
        )
    }
}

AvailabilityPage.propTypes = {
    maxworkstationcount: PropTypes.number,
    timestamp: PropTypes.number,
    scope: PropTypes.object,
    links: PropTypes.object
}

export default AvailabilityPage

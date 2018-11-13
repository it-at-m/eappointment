/* global window */
/* global confirm */
/* global alert */
import React, { Component, PropTypes } from 'react'
import $ from 'jquery'
import moment from 'moment'
import AvailabilityForm from './form'
import Conflicts from './conflicts'
import TimeTable from './timetable'
import UpdateBar from './updateBar'
import SaveBar from './saveBar'

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

    componentWillMount() {
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
                lastSave: new Date()
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
        const today = moment(this.props.timestamp, 'X').startOf('day')

        const yesterday = today.clone().subtract(1, 'days')
        const tomorrow = today.clone().add(1, 'days')

        const start = moment(availability.startDate, 'X')
        const end = moment(availability.endDate, 'X')

        if (start.isAfter(yesterday, 'day')) {
            alert('Beginn der Öffnungszeit muss mind. 1 Tag zurückliegen.')
            return
        }

        if (end.isBefore(tomorrow, 'day')) {
            alert('Ende der Öffnungszeit muss mind. 1 Tag vorausliegen.')
            return
        }

        const pastAvailability = Object.assign({}, availability, {
            endDate: parseInt(yesterday.format('X'), 10)
        })

        const exceptionAvailability = Object.assign({}, availability, {
            startDate: parseInt(today.format('X'), 10),
            endDate: parseInt(today.format('X'), 10),
            tempId: tempId(),
            id: null,
            description: `Ausnahme für ${formatTimestampDate(this.props.timestamp)}`
        })

        const futureAvailability = Object.assign({}, availability, {
            startDate: parseInt(tomorrow.format('X'), 10),
            tempId: tempId(),
            id: null
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
        const today = moment(this.props.timestamp, 'X').startOf('day')
        const yesterday = today.clone().subtract(1, 'days')

        const start = moment(availability.startDate, 'X')

        if (start.isAfter(yesterday, 'day')) {
            alert('Beginn der Öffnungszeit muss mind. 1 Tag zurückliegen.')
            return
        }

        const pastAvailability = Object.assign({}, availability, {
            endDate: parseInt(yesterday.format('X'), 10)
        })

        const futureAvailability = Object.assign({}, availability, {
            startDate: parseInt(today.format('X'), 10),
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

        const todaysAvailabilities = this.state.availabilitylist.filter(availability => {
            const start = moment(availability.startDate, 'X')
            const end = moment(availability.endDate, 'X')
            const today = moment(this.props.timestamp, 'X').startOf('day')

            return start.isSameOrBefore(today) && end.isSameOrAfter(today)
        })


        return <TimeTable
            timestamp={this.props.timestamp}
            conflicts={this.state.conflicts}
            availabilities={todaysAvailabilities}
            availabilityListSlices={this.state.availabilitylistslices}
            maxWorkstationCount={this.props.maxworkstationcount}
            links={this.props.links}
            onSelect={onSelect}
            onNewAvailability={this.onNewAvailability.bind(this)}
        />
    }

    renderForm() {
        if (this.state.selectedAvailability) {
            return <AvailabilityForm data={this.state.selectedAvailability}
                title={this.state.formTitle}
                onSave={this.onUpdateAvailability.bind(this)}
                onPublish={this.onPublishAvailability.bind(this)}
                onDelete={this.onDeleteAvailability.bind(this)}
                onAbort={this.onRevertUpdates.bind(this)}
                onCopy={this.onCopyAvailability.bind(this)}
                onException={this.onCreateExceptionForAvailability.bind(this)}
                onEditInFuture={this.onEditAvailabilityInFuture.bind(this)}
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
    conflicts: PropTypes.array,
    availabilitylist: PropTypes.array,
    availabilitylistslices: PropTypes.array,
    maxworkstationcount: PropTypes.number,
    timestamp: PropTypes.number,
    scope: PropTypes.object,
    links: PropTypes.object
}

export default AvailabilityPage

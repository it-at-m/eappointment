/* global confirm */
import React, { Component, PropTypes } from 'react'
import $ from 'jquery'

import AvailabilityForm from './form'
import Conflicts from './conflicts'
import TimeTable from './timetable'
import UpdateBar from './updateBar'

import PageLayout from './layouts/page'

const tempId = (() => {
    let lastId = -1

    return () => {
        lastId += 1
        return `__temp__${lastId}`
    }
})()

const getStateFromProps = props => {
    return {
        availabilitylist: props.availabilitylist.map(item => {
            return Object.assign({}, item, {
                maxSlots: props.maxslots[item.id] || 0,
                busySlots: props.busyslots[item.id] || 0
            })
        })
    }
}

const mergeAvailabilityListIntoState = (state, list) => list.reduce(updateAvailabilityInState, state)

const updateAvailabilityInState = (state, newAvailability) => {
    let updated = false

    const newState = Object.assign({}, state, {
        availabilitylist: state.availabilitylist.map(availabilty => {
            if (availabilty.id === newAvailability.id) {
                updated = true
                return newAvailability
            } else {
                return availabilty
            }
        })
    })

    if (!updated) {
        newState.availabilitylist.push(newAvailability)
    }

    newState.stateChanged = true

    return newState
}

const deleteAvailabilityInState = (state, deleteAvailability) => {
    return Object.assign({}, state, {
        stateChanged: true,
        availabilitylist: state.availabilitylist.filter(availabilty => availabilty.id !== deleteAvailability.id)
    })
}

const getInitialState = (props) => Object.assign({}, {
    availabilitylist: [],
    selectedAvailability: null,
    stateChanged: false
}, getStateFromProps(props))

class AvailabilityPage extends Component {
    constructor(props) {
        super(props)
        this.state = getInitialState(props)
    }

    onUpdateAvailability(availability) {
        this.setState(Object.assign({}, updateAvailabilityInState(this.state, availability), {
            selectedAvailability: availability
        }))
    }

    onSaveUpdates() {

        const sendData = this.state.availabilitylist.map(availability => {
            if (/^___temp___\d+$/.test(availability.id)) {
                return Object.assign({}, availability, { id: null })
            }
        })

        console.log('save updates', sendData)

        $.ajax('/availability/', {
            method: 'POST',
            data: JSON.stringify(sendData)
        }).done((success) => {
            console.log('save success', success)
            if (success.data) {
                this.setState(mergeAvailabilityListIntoState(this.state, success.data))
            }
        }).fail((err) => {
            console.log('save error', err)
        })
    }

    onDeleteAvailability(availability) {
        console.log('delete', availability)
        const ok = confirm('Soll diese Öffnungszeit wirklich gelöscht werden?')
        const id = availability.id
        if (ok) {
            $.ajax(`/availability/${id}`, {
                method: 'DELETE'
            }).done(() => {
                this.setState(Object.assign({}, deleteAvailabilityInState(this.state, availability), {
                    selectedAvailability: null
                }))
            }).fail(err => {
                console.log('delete error', err)
            })
        }
    }

    onCopyAvailability(availability) {
        this.setState({
            selectedAvailability: Object.assign({}, availability, { id: tempId() })
        })
    }

    renderTimeTable() {
        const onSelect = data => {
            this.setState({
                selectedAvailability: data
            })
        }

        return <TimeTable
                   timestamp={this.props.timestamp}
                   conflicts={this.props.conflicts}
                   availabilities={this.state.availabilitylist}
                   maxWorkstationCount={this.props.maxworkstationcount}
                   links={this.props.links}
                   onSelect={onSelect} />
    }

    renderForm() {
        if (this.state.selectedAvailability) {
            return <AvailabilityForm data={this.state.selectedAvailability}
                       onSave={this.onUpdateAvailability.bind(this)}
                       onDelete={this.onDeleteAvailability.bind(this)}
                       onCopy={this.onCopyAvailability.bind(this)}
                   />
        }
    }

    renderUpdateBar() {
        if (this.state.stateChanged) {
            return <UpdateBar onSave={this.onSaveUpdates.bind(this)}/>
        }
    }

    render() {
        return (
            <PageLayout
                timeTable={this.renderTimeTable()}
                updateBar={this.renderUpdateBar()}
                form={this.renderForm()}
                conflicts={<Conflicts />}
            />
        )
    }
}

AvailabilityPage.propTypes = {
    conflicts: PropTypes.array,
    availabilitylist: PropTypes.array,
    maxworkstationcount: PropTypes.number,
    timestamp: PropTypes.number,
    scope: PropTypes.object,
    links: PropTypes.object
}

export default AvailabilityPage

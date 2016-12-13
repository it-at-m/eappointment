/* global confirm */
import React, { Component, PropTypes } from 'react'
import $ from 'jquery'

import AvailabilityForm from './form'
import Conflicts from './conflicts'
import TimeTable from './timetable'

import PageLayout from './layouts/page'

const getStateFromProps = props => {
    return {
        availabilitylist: props.availabilitylist.map(item => {
            return Object.assign({}, item, {
                maxSlots: props.maxslots[item.id] || 0,
                busySlots: props.busyslots[item.id] || 0
            })
        }),
        selectedAvailability: null
    }
}

const updateAvailabilityInProps = (props, newAvailabilty) => {
    return Object.assign({}, props, {
        availabilitylist: props.availabilitylist.map(availabilty => {
            return availabilty.id === newAvailabilty.id ? newAvailabilty : availabilty
        })
    })
}

class AvailabilityPage extends Component {
    constructor(props) {
        super(props)
        this.state = getStateFromProps(props)
    }

    onSaveAvailability(availability) {
        console.log('save', availability)
        $.ajax('/availability/', {
            method: 'POST',
            data: JSON.stringify(availability)
        }).done(() => {
            console.log('save success')
            this.setState(getStateFromProps(updateAvailabilityInProps(this.props, availability)))
        }).fail(() => {
            console.log('save failure')
        })
    }

    onChangeAvailability(availability) {
        console.debug('change', availability)
    }

    onDeleteAvailability(availability) {
        console.log('delete', availability)
        const ok = confirm('Soll diese Öffnungszeit wirklich gelöscht werden?')
        const id = availability.id
        if (ok) {
            $.ajax(`/availability/${id}`, {
                method: 'DELETE'
            }).done(() => {
                this.options.removeAvailability(id)
                this.$.hide();
            }).fail(err => {
                console.log('ajax error', err)
            })
        } 
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
                       onSave={this.onSaveAvailability.bind(this)}
                       onDelete={this.onDeleteAvailability.bind(this)}
                       onChange={this.onChangeAvailability.bind(this)}
                   />
        }
    }

    render() {
        return (
            <PageLayout
                timeTable={this.renderTimeTable()}
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

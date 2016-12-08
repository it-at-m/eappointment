import React, { Component, PropTypes } from 'react'

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

class AvailabilityPage extends Component {
    constructor(props) {
        super(props)
        this.state = getStateFromProps(props)
    }

    renderTimeTable() {
        const onSelect = data => {
            console.log('onSelect', data)
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
            return <AvailabilityForm data={this.state.selectedAvailability} />
        }
    }

    render() {
        console.log('AvailabilityPage', this.props)
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

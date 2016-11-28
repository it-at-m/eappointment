import React, { Component, PropTypes } from 'react'

import AvailabilityForm from './form'
import Conflicts from './conflicts'
import TimeTable from './timetable'

import PageLayout from './layouts/page'

class AvailabilityPage extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }

    renderTimeTable() {
        return <TimeTable
                   timestamp={this.props.timestamp}
                   conflicts={this.props.conflicts}
                   links={this.props.links}/>
    }

    render() {
        console.log('AvailabilityPage', this.props)
        return (
            <PageLayout
                timeTable={this.renderTimeTable()}
                form={<AvailabilityForm />}
                conflicts={<Conflicts />}
            />
        )
    }
}

AvailabilityPage.propTypes = {
    conflicts: PropTypes.array,
    timestamp: PropTypes.number,
    scope: PropTypes.object,
    links: PropTypes.object
}

export default AvailabilityPage

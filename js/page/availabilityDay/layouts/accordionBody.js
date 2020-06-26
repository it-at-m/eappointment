import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales';
import AvailabilityForm from '../form'
import Board from '../layouts/board'
import {weekDayList, availabilityTypes} from '../helpers'
moment.locale('de')

class AccordionComponent extends Component {
    constructor(props) {
        super(props);
    }

    renderAccordion() {
        return this.props.availabilities.map((availability, key) => {
            const startDate = moment(availability.startDate, 'X').format('DD.MM.YYYY');
            const endDate = moment(availability.endDate, 'X').format('DD.MM.YYYY');
            const startTime = moment(availability.startTime, 'h:mm:ss').format('HH:mm');
            const endTime = moment(availability.endTime, 'h:mm:ss').format('HH:mm');
            const availabilityType = availabilityTypes.find(element => element.value == availability.type).name
            const title = `Bearbeiten von ${availability.id} (${startDate} - ${endDate})`
            const availabilityWeekDayList = Object.keys(availability.weekday).filter(key => parseInt(availability.weekday[key], 10) > 0)
            const availabilityWeekDay = weekDayList.filter(element => availabilityWeekDayList.includes(element.value)).map(item => item.label).join(', ')

            return (
                <div key={key}> 
                    <h3 className="accordion__heading" title={title}>
                        <button className="accordion__trigger" aria-expanded={availability.id == this.props.data.id}>
                            <span className="accordion__title">{startDate} - {endDate}, {startTime} - {endTime} ({availabilityType}, {availabilityWeekDay})</span>
                        </button>
                    </h3>
                    <div className="accordion__panel">
                        <AvailabilityForm data={this.props.data}
                            today={this.props.today}
                            timestamp={this.props.timestamp}
                            title={this.props.formTitle}
                            onSave={this.props.onSave(availability)}
                            onPublish={this.props.onPublish(availability)}
                            onDelete={this.props.onDelete(availability)}
                            onAbort={this.props.onAbort}
                            onCopy={this.props.onCopy(availability)}
                            onException={this.props.onException(availability)}
                            onEditInFuture={this.props.onEditInFuture(availability)}
                            handleFocus={this.props.handleFocus}
                        />
                    </div>
                </div>
            )
        })
    }

    renderBody() {
        return (
            <div id="availabilityAccordion" className="accordion js-accordion">
                {this.renderAccordion()}
            </div>
        );
    }

    render() {
        return (
            <Board title=""
            headerRight=""
            body= {this.renderBody()}
            footer="" />
        )
       
    }
}

AccordionComponent.defaultProps = {
    data: {}
}

AccordionComponent.propTypes = {
    onSave: PropTypes.func,
    onPublish: PropTypes.func,
    onDelete: PropTypes.func,
    onAbort: PropTypes.func,
    onCopy: PropTypes.func,
    onException: PropTypes.func,
    onEditInFuture: PropTypes.func,
    handleFocus: PropTypes.func,
    data: PropTypes.object,
    availabilities: PropTypes.array,
    timestamp: PropTypes.number,
    formTitle: PropTypes.string,
    today: PropTypes.number
}

export default AccordionComponent

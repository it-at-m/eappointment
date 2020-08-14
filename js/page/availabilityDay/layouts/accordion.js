import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales';
import AvailabilityForm from '../form'
import FooterButtons from '../form/footerButtons'
import {weekDayList, availabilityTypes} from '../helpers'
import Board from './board'
moment.locale('de')

class Accordion extends Component 
{
    constructor(props) {
        super(props);
    }
    
    render() {
        const onPublish = (ev) => {
            ev.preventDefault()
            let validationResult = validate(data, this.props)
            if (!data.__modified || validationResult.valid) {
                this.props.onPublish(getDataValuesFromForm(data, this.props.data.scope))
            } else {
                this.setState({ errors: validationResult.errors })
                this.handleFocus(this.errorElement);
            }
        }

        const onDelete = ev => {
            ev.preventDefault()
            this.props.onDelete(getDataValuesFromForm(data, this.props.data.scope))
        }

        const onAbort = (ev) => {
            ev.preventDefault()
            this.props.onAbort()
        }

        const onNewClick = (ev) => {
            ev.preventDefault()
            this.props.onNewAvailability()
        }
        
        const renderAccordionBody = () => {
            return this.props.availabilities.map((availability, index) => {
                const startDate = moment(availability.startDate, 'X').format('DD.MM.YYYY');
                const endDate = moment(availability.endDate, 'X').format('DD.MM.YYYY');
                const startTime = moment(availability.startTime, 'h:mm:ss').format('HH:mm');
                const endTime = moment(availability.endTime, 'h:mm:ss').format('HH:mm');
                const availabilityType = availabilityTypes.find(element => element.value == availability.type).name
                const title = `Bearbeiten von ${availability.id} (${startDate} - ${endDate})`
                const availabilityWeekDayList = Object.keys(availability.weekday).filter(key => parseInt(availability.weekday[key], 10) > 0)
                const availabilityWeekDay = weekDayList.filter(element => availabilityWeekDayList.includes(element.value)
                ).map(item => item.label).join(', ')
        
                const isExpanded = (this.props.data) ? availability.id == this.props.data.id : false;
        
                const onClick = ev => {
                    ev.preventDefault()
                    this.props.onSelect(availability)
                    if (this.props.data && ev.target.attributes.eventkey.value == this.props.data.id) {
                        this.props.onSelect(null)
                    }
                }
        
                return (
                    <section key={index} className="accordion-section">
                        <h3 className="accordion__heading" role="heading" title={title}>
                            <button eventkey={availability.id} onClick={onClick} className="accordion__trigger" aria-expanded={isExpanded}>
                                <span className="accordion__title">{startDate} - {endDate}, {startTime} - {endTime} ({availabilityType}, {availabilityWeekDay})</span>
                            </button>
                        </h3>
                        <div className={isExpanded ? "accordion__panel opened" : "accordion__panel"} hidden={(isExpanded) ? "" : "hidden"}>
                            <AvailabilityForm data={availability}
                                today={this.props.today}
                                title={this.props.formTitle}
                                onCopy={this.props.onCopy}
                                onException={this.props.onException}
                                onEditInFuture={this.props.onEditInFuture}
                                handleFocus={this.props.handleFocus}
                            />
                        </div>
                    </section>
                )
            })
        }

        return (
            <Board className="accordion js-accordion"
                title=""
                body={renderAccordionBody()}
                footer={<FooterButtons data={this.props.data} {...{onNewClick, onPublish, onDelete, onAbort }} />}
            />
        )
    }
}

Accordion.propTypes = {
    data: PropTypes.object,
    onChange: PropTypes.func,
    onCopy: PropTypes.func,
    onException: PropTypes.func,
    onEditInFuture: PropTypes.func,
    handleFocus: PropTypes.func
}

export default Accordion

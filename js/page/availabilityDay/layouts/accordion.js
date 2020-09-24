import React, { Component} from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales';
import AvailabilityForm from '../form'
import FooterButtons from '../form/footerButtons'
import {accordionTitle} from '../helpers'
import Board from './board'
moment.locale('de')

class Accordion extends Component 
{
    constructor(props) {
        super(props);
        this.isExpanded = null
    } 

    componentDidUpdate(prevProps) {
        var eventId = null
        if (prevProps.data !== this.props.data) {
            if (this.props.data) {
                eventId = (this.props.data.id) ? this.props.data.id : this.props.data.tempId;
            }
            this.isExpanded = eventId
        }
    }
    
    render() {
        const onPublish = (ev) => {
            ev.preventDefault()
            this.props.onPublish()
        }

        const onAbort = (ev) => {
            ev.preventDefault()
            this.props.onAbort()
        }

        const onNew = (ev) => {
            ev.preventDefault()
            this.props.onNew()
        }
        
        const renderAccordionBody = () => {
            return this.props.availabilities.map((availability, index) => {
                if (! availability.id && ! availability.tempId) {
                    availability.tempId = `spontaneous_ID_${index}`
                }
                let eventId = availability.id ? availability.id : availability.tempId;
            
                const onToggle = ev => {
                    ev.preventDefault();
                    if (eventId == ev.currentTarget.attributes.eventkey.value && eventId != this.isExpanded) {
                        this.isExpanded = eventId
                        this.props.onSelect(availability)
                    } else {
                        this.isExpanded = null
                        this.props.onSelect(null)
                    }
                }

                const onCopy = ev => {
                    ev.preventDefault()
                    this.props.onCopy(availability)
                }

                const onExclusion = ev => {
                    ev.preventDefault()
                    this.props.onExclusion(availability)
                }

                const onEditInFuture = ev => {
                    ev.preventDefault()
                    this.props.onEditInFuture(availability)
                }

                const onDelete = ev => {
                    ev.preventDefault()
                    this.props.onDelete(availability)
                }
        
                let title = accordionTitle(availability);

                let hasConflict = (
                    Object.keys(this.props.conflictList.itemList).length > 0 && 
                    this.props.conflictList.conflictIdList.includes(eventId) 
                )

                let hasError = (
                    Object.keys(this.props.errorList).length > 0 && 
                    this.props.errorList.find(error => error.id == eventId)
                )

                let conflictList = []
                Object.keys(this.props.conflictList.itemList).map(date => {
                    (this.props.conflictList.itemList[date].map(conflict => {
                        if (conflict.appointments[0].availability == eventId) {
                            if (! conflictList[date]) {
                                conflictList[date] = [];
                            }
                            conflictList[date].push(conflict); 
                        }
                    }))
                })
                return (
                    <section key={index} className="accordion-section" style={hasConflict || hasError ? { border: "1px solid #9B0000"} : null}>
                        <h3 className="accordion__heading" role="heading" title={title}>
                            <button eventkey={eventId} onClick={onToggle} className="accordion__trigger" aria-expanded={eventId == this.isExpanded}>
                                <span className="accordion__title">{title}</span>
                            </button>
                        </h3>
                        <div className={eventId == this.isExpanded ? "accordion__panel opened" : "accordion__panel"} hidden={eventId == this.isExpanded ? "" : "hidden"}>
                            <AvailabilityForm 
                                data={availability}
                                selectedAvailability={this.props.data}
                                availabilityList={this.props.availabilities}
                                today={this.props.today}
                                timestamp={this.props.timestamp}
                                handleChange={this.props.handleChange}
                                onCopy={onCopy}
                                onExclusion={onExclusion}
                                onEditInFuture={onEditInFuture}
                                onDelete={onDelete}
                                errorList={hasError ? this.props.errorList : []}
                                conflictList={hasConflict ? Object.assign({}, conflictList): {}}
                                setErrorRef={this.props.setErrorRef}
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
                footer={<FooterButtons 
                    hasConflicts={Object.keys(this.props.conflictList.itemList).length || this.props.errorList.length ? true : false}
                    stateChanged={this.props.stateChanged} 
                    data={this.props.data} 
                    {...{onNew, onPublish, onAbort }} 
                />}
            />
        )
    }
}

Accordion.propTypes = {
    data: PropTypes.object,
    availabilities: PropTypes.array,
    errorList: PropTypes.array,
    conflictList: PropTypes.object,
    today: PropTypes.number,
    timestamp: PropTypes.number,
    onSelect: PropTypes.func,
    handleChange: PropTypes.func,
    onPublish: PropTypes.func,
    onDelete: PropTypes.func,
    onAbort: PropTypes.func,
    onNew: PropTypes.func,
    onCopy: PropTypes.func,
    onExclusion: PropTypes.func,
    onEditInFuture: PropTypes.func,
    setErrorRef: PropTypes.func,
    stateChanged: PropTypes.bool,
    errorElement: PropTypes.element
}

export default Accordion

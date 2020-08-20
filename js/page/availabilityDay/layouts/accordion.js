import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales';
import AvailabilityForm from '../form'
import validate from '../form/validate'
import FooterButtons from '../form/footerButtons'
import {accordionTitle, getFormValuesFromData} from '../helpers'
import Board from './board'
moment.locale('de')

class Accordion extends Component 
{
    constructor(props) {
        super(props);
        this.errorElement = null;
        this.errors = {}
        this.setErrorRef = element => {
            this.errorElement = element
        };
    }
    
    render() {
        const onPublish = (ev) => {
            ev.preventDefault()
            let validationResult = validate(this.props.data, this.props)
            if (!this.props.data.__modified || validationResult.valid) {
                this.props.onPublish(this.props.data)
            } else {
                this.props.handleErrorList(validationResult.errors);
                this.props.handleFocus(this.errorElement);
            }
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
                let eventId = availability.id ? availability.id : availability.tempId;
                const isExpanded = (this.props.data) ? (eventId == this.props.data.id || eventId == this.props.data.tempId) : false;

                const onToggle = ev => {
                    ev.preventDefault()
                    this.props.onSelect(availability)
                    if (this.props.data && ev.target.attributes.eventkey.value == eventId) {
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
                return (
                    <section key={index} className="accordion-section">
                        <h3 className="accordion__heading" role="heading" title={title}>
                            <button eventkey={availability.id || availability.tempId} onClick={onToggle} className="accordion__trigger" aria-expanded={isExpanded}>
                                <span className="accordion__title">{title}</span>
                            </button>
                        </h3>
                        <div className={isExpanded ? "accordion__panel opened" : "accordion__panel"} hidden={(isExpanded) ? "" : "hidden"}>
                            <AvailabilityForm 
                                data={availability}
                                availabilityList={this.props.availabilities}
                                today={this.props.today}
                                timestamp={this.props.timestamp}
                                handleFocus={this.props.handleFocus}
                                handleChange={this.props.handleChange}
                                onCopy={onCopy}
                                onExclusion={onExclusion}
                                onEditInFuture={onEditInFuture}
                                onDelete={onDelete}
                                includeUrl={this.props.includeUrl}
                                errorList={this.props.errorList}
                                setErrorRef={this.setErrorRef}
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
    errorList: PropTypes.object,
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
    handleFocus: PropTypes.func,
    handleErrorList: PropTypes.func,
    availabilities: PropTypes.array,
    stateChanged: PropTypes.bool,
    includeUrl: PropTypes.string,
    errorElement: PropTypes.element
}

export default Accordion

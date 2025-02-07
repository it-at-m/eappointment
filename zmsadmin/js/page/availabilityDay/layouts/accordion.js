import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales';
import AvailabilityForm from '../form'
import FooterButtons from '../form/footerButtons'
import { accordionTitle } from '../helpers'
import Board from './board'
import { hasSlotCountError } from '../form/validate';
moment.locale('de')

class Accordion extends Component {
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
        const hasNewAvailability = this.props.availabilityList.some(availability =>
        (availability?.tempId?.includes('__temp__') ||
            availability?.kind === 'exclusion')
        );

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

        const hasConflict = (eventId) => {
            return (
                Object.keys(this.props.conflictList.itemList).length > 0 &&
                this.props.conflictList.conflictIdList.includes(eventId)
            )
        }

        const hasError = (eventId) => {
            return (
                Object.keys(this.props.errorList).length > 0 &&
                Object.values(this.props.errorList).find(item => item.id == eventId)
            )
        }

        const renderAccordionBody = () => {
            return this.props.availabilityList.map((availability, index) => {
                if (!availability.id && !availability.tempId) {
                    availability.tempId = `spontaneous_ID_${index}`
                }
                let eventId = availability.id ? availability.id : availability.tempId;

                let accordionExpanded =
                    (availability.id && availability.id === this.isExpanded) ||
                    (availability.tempId && availability.tempId === this.isExpanded);

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

                const onUpdateSingle = ev => {
                    ev.preventDefault()
                    this.props.onUpdateSingle(availability)
                }

                let title = accordionTitle(availability);

                let conflictList = []
                Object.keys(this.props.conflictList.itemList).map(date => {
                    (this.props.conflictList.itemList[date].map(conflict => {
                        if (conflict.appointments[0].availability == eventId) {
                            if (!conflictList[date]) {
                                conflictList[date] = [];
                            }
                            conflictList[date].push(conflict);
                        }
                    }))
                })
                let errorList = []
                Object.values(this.props.errorList).map(item => {
                    if (item.id == eventId) {
                        errorList.push(item);
                    }
                })

                return (
                    <section key={index} className="accordion-section" style={hasConflict(eventId) || hasError(eventId) ? { border: "1px solid #9B0000" } : null}>
                        <h3 className="accordion__heading" role="heading" title={title}>
                            <button
                                eventkey={eventId}
                                onClick={onToggle}
                                className="accordion__trigger"
                                aria-expanded={accordionExpanded}
                                style={(() => {
                                    // Helper function to check description text
                                    const hasDescriptionText = (text) =>
                                        availability?.description?.includes(text);

                                    // Check both kind and description text
                                    if (availability?.kind === 'origin' || hasDescriptionText('Ursprüngliche Serie')) {
                                        return { backgroundColor: '#CCE5FF' };
                                    }
                                    if (availability?.kind === 'future' || hasDescriptionText('Fortführung der Terminserie')) {
                                        return { backgroundColor: '#94c5a2' };
                                    }
                                    if (availability?.kind === 'exclusion' || hasDescriptionText('Ausnahme zu Terminserie')) {
                                        return { backgroundColor: '#FFE082' };
                                    }
                                    if (availability?.kind === 'new') {
                                        return { backgroundColor: '#F5F5DC' };
                                    }
                                    return null;
                                })()}
                            >
                                <span className="accordion__title">{title}</span>
                            </button>
                        </h3>
                        <div className={accordionExpanded ? "accordion__panel opened" : "accordion__panel"} hidden={accordionExpanded ? "" : "hidden"}>
                            <AvailabilityForm
                                data={availability}
                                selectedAvailability={this.props.data}
                                availabilityList={this.props.availabilityList}
                                today={this.props.today}
                                selectedDate={moment(this.props.timestamp, 'X').startOf('day').unix()}
                                handleChange={this.props.handleChange}
                                setErrorRef={this.props.setErrorRef}
                                onCopy={onCopy}
                                onExclusion={onExclusion}
                                onEditInFuture={onEditInFuture}
                                onUpdateSingle={onUpdateSingle}
                                onDelete={onDelete}
                                errorList={hasError(eventId) ? errorList : {}}
                                conflictList={hasConflict(eventId) ? Object.assign({}, conflictList) : {}}
                                isCreatingExclusion={this.props.isCreatingExclusion || hasNewAvailability}  // Pass combined flag
                            />
                        </div>
                    </section>
                )
            })
        }

        return (
            <>
                {/* Legend */}
                <div style={{
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: '10px',
                    margin: '20px 0'
                }}>
                    <div style={{
                        display: 'flex',
                        justifyContent: 'center',
                        alignItems: 'center',
                        gap: '20px'
                    }}>
                        {[
                            { color: '#F0F0F0', label: 'Nicht geänderte Öffnungszeit' },
                            { color: '#CCE5FF', label: 'Ursprüngliche Serie' },
                            { color: '#94c5a2', label: 'Fortführung der Serie' },
                            { color: '#FFE082', label: 'Ausnahme' },
                            { color: '#F5F5DC', label: 'Neue Öffnungszeit' }
                        ].map(({ color, label }) => (
                            <div key={label} style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                <div style={{
                                    width: '16px',
                                    height: '16px',
                                    backgroundColor: color,
                                    border: '1px solid #ccc'
                                }} />
                                <span>{label}</span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Accordion */}
                <Board className="accordion js-accordion"
                    title=""
                    body={renderAccordionBody()}
                    footer={<FooterButtons
                        hasConflicts={Object.keys(this.props.conflictList.itemList).length ? true : false}
                        hasErrors={Object.values(this.props.errorList).some(error => {
                            const hasPastTimeError = error.itemList?.flat(2)
                                .some(item => item?.type === 'endTimePast');
                            return !hasPastTimeError;
                        })}
                        hasSlotCountError={hasSlotCountError(this.props)}
                        stateChanged={this.props.stateChanged}
                        data={this.props.data}
                        availabilitylist={this.props.availabilityList}
                        {...{ onNew, onPublish, onAbort }}
                    />}
                />
            </>
        )
    }
}

Accordion.propTypes = {
    data: PropTypes.object,
    availabilityList: PropTypes.array,
    errorList: PropTypes.object,
    conflictList: PropTypes.object,
    today: PropTypes.number,
    timestamp: PropTypes.number,
    onSelect: PropTypes.func,
    handleChange: PropTypes.func,
    onPublish: PropTypes.func,
    onDelete: PropTypes.func,
    onUpdateSingle: PropTypes.func,
    onAbort: PropTypes.func,
    onNew: PropTypes.func,
    onCopy: PropTypes.func,
    onExclusion: PropTypes.func,
    onEditInFuture: PropTypes.func,
    setErrorRef: PropTypes.func,
    stateChanged: PropTypes.bool,
    errorElement: PropTypes.element,
    isCreatingExclusion: PropTypes.bool
}

export default Accordion
import React from 'react'
import moment from 'moment/min/moment-with-locales';
import AvailabilityForm from '../form'
import {weekDayList, availabilityTypes} from '../helpers'
import Board from '../layouts/board'
moment.locale('de')

const Accordion = (props) => {
    return (
        <Board className="accordion js-accordion"
            title=""
            body={AccordionBody(props)}
        />
    )
}

const AccordionBody = (props) => {

    return props.availabilities.map((availability, index) => {
        const startDate = moment(availability.startDate, 'X').format('DD.MM.YYYY');
        const endDate = moment(availability.endDate, 'X').format('DD.MM.YYYY');
        const startTime = moment(availability.startTime, 'h:mm:ss').format('HH:mm');
        const endTime = moment(availability.endTime, 'h:mm:ss').format('HH:mm');
        const availabilityType = availabilityTypes.find(element => element.value == availability.type).name
        const title = `Bearbeiten von ${availability.id} (${startDate} - ${endDate})`
        const availabilityWeekDayList = Object.keys(availability.weekday).filter(key => parseInt(availability.weekday[key], 10) > 0)
        const availabilityWeekDay = weekDayList.filter(element => availabilityWeekDayList.includes(element.value)
        ).map(item => item.label).join(', ')

        const isExpanded = (props.data) ? availability.id == props.data.id : false;

        const onClick = ev => {
            ev.preventDefault()
            props.onSelect(availability)
            if (props.data && ev.target.attributes.eventkey.value == props.data.id) {
                props.onSelect(null)
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
                        today={props.today}
                        title={props.formTitle}
                        onSave={props.onSave}
                        onPublish={props.onPublish}
                        onDelete={props.onDelete}
                        onAbort={props.onAbort}
                        onCopy={props.onCopy}
                        onException={props.onException}
                        onEditInFuture={props.onEditInFuture}
                        handleFocus={props.handleFocus}
                    />
                </div>
            </section>
        )
    })
}

export default Accordion

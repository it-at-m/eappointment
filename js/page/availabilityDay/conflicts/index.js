import React, { PropTypes } from 'react'
import Board from '../layouts/board'
import moment from 'moment'

const renderLink = (conflict, onClick) => {
    const appointment = conflict.appointments[0]
    const availability = appointment.availability || {}
    const startTime = moment(appointment.date, 'X').format('YYYY-MM-DD HH:mm')
    const slotTime = availability.slotTimeInMinutes || 0
    const endTime = moment(appointment.date + slotTime * 60 * appointment.slotCount, 'X').format('HH:mm')
    if (availability.id) {
        return <a href="#" onClick={onClick}><strong>{startTime} - {endTime}</strong></a>
    } else {
        return <span><strong>{startTime}</strong></span>
    }
}

const renderConflicts = (conflicts, onSelect) => {
    if (conflicts.length > 0) {
        return conflicts.map((conflict, key) => {

            const onClick = ev => {
                ev.preventDefault()
                const appointment = conflict.appointments[0]
                const availability = appointment.availability || {}
                onSelect(availability.id)
            }

            return (
                <span key={key}>
                    {renderLink(conflict, onClick)}
                    {conflict.queue.withAppointment
                     ? <p>Termin außerhalb der Öffnungszeiten oder überbucht</p>
                     : <p>{conflict.amendment}</p>
                    }
                </span>
            )
        })
    } else {
        return (
            <p>Keine Konflikte</p>
        )
    }
}

const Conflicts = (props) => {
    return (
        <Board className="availability-conflicts"
            title="Konflikte"
            body={renderConflicts(props.conflicts, props.onSelect)} 
        />
    )
}

Conflicts.defaultProps = {
    conflicts: []
}

Conflicts.propTypes = {
    conflicts: PropTypes.array,
    onSelect: PropTypes.func.isRequired
}

export default Conflicts


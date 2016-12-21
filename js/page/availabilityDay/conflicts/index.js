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
        return <a href="#" onClick={onClick}>{startTime} - {endTime}</a>
    } else {
        return <span>{startTime}</span>
    }
}

const renderConflicts = (conflicts, onSelect) => {
    if (conflicts.length > 0) {
        return conflicts.map((conflict, key) => {

            const onClick = ev => {
                ev.preventDefault()
                onSelect(conflict.availability.id)
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


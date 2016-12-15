import React, { PropTypes } from 'react'
import Board from '../layouts/board'
import moment from 'moment'

const renderConflicts = conflicts => {
    if (conflicts.length > 0) {
        return conflicts.map((conflict, key) => {

            const appointment = conflict.appointments[0]
            const startTime = moment(appointment.date, 'X').format('YYYY-MM-DD HH:mm')
            const availability = appointment.availability || {}
            const slotTime = availability.slotTimeInMinutes || 0
            const endTime = moment(appointment.date + slotTime * 60 * appointment.slotCount, 'X').format('HH:mm')

            return (
                <span key={key}>
                    <a href='#'>{startTime} - {endTime}</a>
                    {conflict.queue.withAppointment
                     ? <p>Termin außerhalb der Öffnungszeiten oder überbucht</p>
                     : <p title={ appointment.availability} >{conflict.amendment}</p>
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
            body={renderConflicts(props.conflicts)} 
        />
    )
}

Conflicts.defaultProps = {
    conflicts: []
}

Conflicts.propTypes = {
    conflicts: PropTypes.array
}

export default Conflicts


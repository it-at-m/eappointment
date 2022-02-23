import React from 'react'
import PropTypes from 'prop-types'


import Board from '../layouts/board'
import moment from 'moment'

const renderLink = (conflict, onClick) => {
    const appointment = conflict.appointments[0]
    const availability = appointment.availability || {}
    const startTime = moment(appointment.date, 'X').format('DD.MM.YYYY HH:mm')
    const slotTime = availability.slotTimeInMinutes || 0
    const endTime = moment(appointment.date + slotTime * 60 * appointment.slotCount, 'X').format('HH:mm')
    if (availability.id) {
        if (moment(appointment.date, 'X').format('HH:mm') === endTime) {
                return <a href="#" onClick={onClick}><strong>{startTime} Uhr</strong></a>
        } else {
              return <a href="#" onClick={onClick}><strong>{startTime} - {endTime} Uhr</strong></a>
        }

    } else {
        return <span><strong>{startTime} Uhr</strong></span>
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
                <div className="message message--error message-keep" role="alert" key={key}>
                    {renderLink(conflict, onClick)}<br />
                    {conflict.amendment}
                </div>
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
        <Board className="board--spaceless availability-conflicts"
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

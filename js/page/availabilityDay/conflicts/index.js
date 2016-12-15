import React, { PropTypes } from 'react'
import Board from '../layouts/board'

const renderConflicts = conflicts => {
    if (conflicts.length > 0) {
        return conflicts.map((conflict, key) => {

            return (
                <span>
                    <a key={key}>{conflict.startTime} - {conflict.endTime}</a>
                    <p>{conflict.description}</p>
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
    console.log("Conflicts", props)
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


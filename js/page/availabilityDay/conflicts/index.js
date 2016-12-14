import React, { PropTypes } from 'react'
import Board from '../layouts/board'

const renderConflicts = conflicts => {
    if (conflicts.length > 0) {
        return conflicts.map((conflict, key) => {
            console.log('conflict', conflict)

            return <a key={key}>Conflict</a>
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


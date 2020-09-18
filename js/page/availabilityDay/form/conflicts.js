import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'

const formatDate = date => {
    const momentDate = moment(date)
    return `${momentDate.format('DD.MM.YYYY')}`
} 

const renderConflictList = conflictList => conflictList.map((collection, index) => {
    return <li key={index}><strong>{formatDate(collection.date)}</strong>
        <ul>
        {
            collection.conflicts.map((conflict, index) => {
                return <li key={index}>{conflict.message} (ID: {conflict.appointments[0].availability})</li>
            })
        }
        </ul>
        
    </li>
})

const Conflicts = (props) => {
    const conflicts = Object.keys(props.conflictList).map(key => {
        return {
            date: key,
            conflicts: props.conflictList[key]
        }
    })
    return (
        conflicts.length ? 
        <div className="message message--error">
            <h3>Folgende Zeiten führen mit der aktuellen Auswahl zu Konflikten:</h3>
            <ul>
            {renderConflictList(conflicts)}
            </ul>
        </div> : null
    )
}

Conflicts.defaultProps = {
    conflictList: {}
}

Conflicts.propTypes = {
    conflictList: PropTypes.object
}

export default Conflicts

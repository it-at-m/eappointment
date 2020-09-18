import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'

const formatDate = date => {
    const momentDate = moment(date)
    return `${momentDate.format('DD.MM.YYYY')}`
} 

const renderConflictList = conflictList => conflictList.map((collection, index) => {
    return (
        <div key={index}>
            <div><strong>{formatDate(collection.date)}</strong></div>
            {
            collection.conflicts.map((conflict, index) => {
                return <div key={index}>- {conflict.message}</div>
            })
            }
        </div>
    )
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
        <div className="message message--error" role="alert">
            <h3>Folgende Zeiten führen mit der aktuellen Auswahl zu Konflikten:</h3>
            {renderConflictList(conflicts)}
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

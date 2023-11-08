import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'

const formatDate = date => {
    const momentDate = moment(date)
    return `${momentDate.format('DD.MM.YYYY')}`
} 

const renderConflictList = (conflictList) => {
    let conflictDatesByMessage = [];
    conflictList.map(collection => {
        collection.conflicts.map((conflict) => {
            if (! conflictDatesByMessage[conflict.message]) {
                Object.assign({}, conflictDatesByMessage[conflict.message] = []);
            }
            conflictDatesByMessage[conflict.message].push(formatDate(collection.date))
        })
    })

    return (
        Object.keys(conflictDatesByMessage).map((key, index) => {
            return (
                <div key={index}>
                    <div><strong>{ conflictDatesByMessage[key].join(", ")  }</strong></div>
                    <div key={index}>- {key}</div>
                </div>
            )
        })
    )
    
    
}

const Conflicts = (props) => {
    const conflicts = Object.keys(props.conflictList).map(key => {
        return {
            date: key,
            conflicts: props.conflictList[key]
        }
    })
    return (
        conflicts.length ? 
        <div className="message message--error" role="alert" aria-live="polite">
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

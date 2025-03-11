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
            const existingConflict = conflictDatesByMessage.find(
                item => item.message === conflict.message
            );

            if (existingConflict) {
                existingConflict.dates.push(formatDate(collection.date));
            } else {
                conflictDatesByMessage.push({
                    message: conflict.message,
                    dates: [formatDate(collection.date)]
                });
            }
        });
    });

    return (
        conflictDatesByMessage.map((item, index) => (
            <div key={index} style={{ marginBottom: '1rem' }}>
                {/* Convert newlines in the message to <br /> tags */}
                <div dangerouslySetInnerHTML={{ __html: item.message.replace(/\n/g, '<br />') }} />
            </div>
        ))
    );
};


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

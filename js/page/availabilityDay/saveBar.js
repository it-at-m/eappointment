import React from 'react'
import moment from 'moment'
import PropTypes from 'prop-types' 

const formatDate = date => {
    const momentDate = moment(date)
    return `${momentDate.format('DD.MM.YYYY')} um ${momentDate.format('HH:mm')} Uhr`
} 
 
const SaveBar = (props) => {
    return (
        <div className="message message--success">
            Öffnungszeiten gespeichert, {formatDate(props.lastSave)}
        </div>
    )
}

SaveBar.propTypes = {
    lastSave: PropTypes.oneOfType([
        PropTypes.number, PropTypes.string
    ]).isRequired
}

export default SaveBar

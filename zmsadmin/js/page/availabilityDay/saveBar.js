import React from 'react'
import moment from 'moment'
import PropTypes from 'prop-types' 

const formatDate = date => {
    const momentDate = moment(date)
    return `${momentDate.format('DD.MM.YYYY')} um ${momentDate.format('HH:mm')} Uhr`
} 
 
const SaveBar = (props) => {
    return (
        <div ref={props.setSuccessRef} className="message message--success">
            Öffnungszeiten gespeichert, {formatDate(props.lastSave)}
        </div>
    )
}

SaveBar.propTypes = {
    lastSave: PropTypes.oneOfType([
        PropTypes.number, PropTypes.string
    ]).isRequired,
    setSuccessRef: PropTypes.func
}

export default SaveBar

import React, { useEffect, useState } from 'react'
import moment from 'moment'
import PropTypes from 'prop-types'

const formatDate = date => {
    const momentDate = moment(date)
    return `${momentDate.format('DD.MM.YYYY')} um ${momentDate.format('HH:mm')} Uhr`
} 

const SaveBar = (props) => {
    const [isVisible, setIsVisible] = useState(true)

    useEffect(() => {
        setIsVisible(true)
        const timer = setTimeout(() => {
            setIsVisible(false)
        }, 5500)
        return () => clearTimeout(timer)
    }, [props.lastSave])

    if (!isVisible) return null

    return (
        <div 
            ref={props.setSuccessRef} 
            className={`message ${props.success ? 'message--success' : 'message--error'}`}
        >
            {props.success 
                ? <b><i className="fas fa-check-circle" aria-hidden="true" aria-label="Erfolg"></i> Öffnungszeiten gespeichert, {formatDate(props.lastSave)}</b>
                : <b><i className="fas fa-times-circle" aria-hidden="true" aria-label="Fehler"></i> Fehler beim Speichern der Öffnungszeiten. Bitte versuchen Sie es erneut.</b>}
        </div>
    )
}

SaveBar.propTypes = {
    lastSave: PropTypes.oneOfType([
        PropTypes.number, 
        PropTypes.string
    ]).isRequired,
    success: PropTypes.bool.isRequired,
    setSuccessRef: PropTypes.func
}

export default SaveBar
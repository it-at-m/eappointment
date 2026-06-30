import React, { useEffect, useState } from 'react'
import moment from 'moment'
import PropTypes from 'prop-types'

const formatDate = date => {
    const momentDate = moment(date)
    return `${momentDate.format('DD.MM.YYYY')} um ${momentDate.format('HH:mm')} Uhr`
} 

export const SAVE_BAR_TTL = 5000;

const SaveBar = (props) => {
    const [isVisible, setIsVisible] = useState(true)

    useEffect(() => {
        setIsVisible(true)
        const timer = setTimeout(() => {
            setIsVisible(false)
        }, SAVE_BAR_TTL)
        return () => clearTimeout(timer)
    }, [props.lastSave])

    if (!isVisible) return null

    const getMessage = () => {
        if (props.type === 'delete') {
            return props.success
                ? <b><i className="fas fa-check-circle" aria-hidden="true" aria-label="Erfolg"></i> Öffnungszeit gelöscht, {formatDate(props.lastSave)}</b>
                : <b><i className="fas fa-times-circle" aria-hidden="true" aria-label="Fehler"></i> Fehler beim Löschen der Öffnungszeit. Bitte versuchen Sie es erneut.</b>
        }

        if (!props.success) {
            return <b><i className="fas fa-times-circle" aria-hidden="true" aria-label="Fehler"></i> Fehler beim Speichern der Öffnungszeiten. Bitte versuchen Sie es erneut.</b>
        }

        if (props.hasConflicts) {
            return <b><i className="fas fa-exclamation-triangle" aria-hidden="true" aria-label="Hinweis"></i> Öffnungszeiten gespeichert, es bestehen jedoch Konflikte. Bitte prüfen Sie die markierte Öffnungszeit, {formatDate(props.lastSave)}</b>
        }

        if (props.conflictCheckFailed) {
            return <b><i className="fas fa-exclamation-triangle" aria-hidden="true" aria-label="Hinweis"></i> Öffnungszeiten gespeichert. Die nachgelagerte Konfliktprüfung konnte nicht abgeschlossen werden. Bitte prüfen Sie die Öffnungszeiten, {formatDate(props.lastSave)}</b>
        }

        return <b><i className="fas fa-check-circle" aria-hidden="true" aria-label="Erfolg"></i> Öffnungszeiten gespeichert, {formatDate(props.lastSave)}</b>
    }

    return (
        <div 
            ref={props.setSuccessRef} 
            className={`message ${
                !props.success
                    ? 'message--error'
                    : (props.hasConflicts || props.conflictCheckFailed)
                        ? 'message--warning'
                        : 'message--success'
            }`}
        >
            {getMessage()}
        </div>
    )
}

SaveBar.propTypes = {
    lastSave: PropTypes.oneOfType([
        PropTypes.number,
        PropTypes.string
    ]).isRequired,
    success: PropTypes.bool.isRequired,
    hasConflicts: PropTypes.bool,
    conflictCheckFailed: PropTypes.bool,
    setSuccessRef: PropTypes.func,
    type: PropTypes.oneOf(['save', 'delete'])
}

SaveBar.defaultProps = {
    type: 'save',
    hasConflicts: false,
    conflictCheckFailed: false
}

export default SaveBar
import React from 'react'
import PropTypes from 'prop-types'

const renderErrors = errors => errors.map(err => {
    return (
        <div key={err[0].fieldName}>
            {err[0].errorMessage}
        </div>
    )
})

const Errors = (props) => {
    const errors = props.errorList.map(error => {
        return Object.entries(error).map(entry => {
            return {
                fieldName: entry[0],
                errorMessage: entry[1]
            }
        })
    })

    return (
        errors.length > 0 ? 
        <div className="message message--error">
            <h3>Folgende Fehler sind bei der Prüfung Ihrer Eingaben aufgetreten:</h3>
            {renderErrors(errors)}
        </div> : null
    )
}

Errors.defaultProps = {
    errorList: []
}

Errors.propTypes = {
    errorList: PropTypes.array
}

export default Errors

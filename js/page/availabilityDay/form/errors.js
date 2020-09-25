import React from 'react'
import PropTypes from 'prop-types'

const renderErrors = errors => errors.map(error => {
    return (
        <div key={error[0].type +'-'+ error[0].id}>
            {error[0].message}
        </div>
    )
})

const Errors = (props) => {
    return (
        props.errorList.length > 0 ? 
        <div className="message message--error">
            <h3>Folgende Fehler sind bei der Prüfung Ihrer Eingaben aufgetreten:</h3>
            {renderErrors(props.errorList)}
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

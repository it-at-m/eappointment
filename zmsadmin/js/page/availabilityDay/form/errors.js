import React from 'react'
import PropTypes from 'prop-types'

const renderErrors = errors => Object.keys(errors).map(key => {
    return (
        <div key={errors[key].id}>
            {errors[key].itemList.map((item, index) => {
                return <div key={index}>{item[0].message}</div>
            })}   
        </div>
    )
})

const Errors = (props) => {
    return (
        Object.keys(props.errorList).length > 0 ? 
        <div className="message message--error" role="alert" aria-live="polite">
            <h3>Folgende Fehler sind bei der Prüfung Ihrer Eingaben aufgetreten:</h3>
            {renderErrors(props.errorList)}
        </div> : null
    )
}

Errors.defaultProps = {
    errorList: []
}

Errors.propTypes = {
    errorList: PropTypes.object
}

export default Errors

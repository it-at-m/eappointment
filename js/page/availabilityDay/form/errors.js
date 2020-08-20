import React from 'react'
import PropTypes from 'prop-types'

const renderErrors = errors => errors.map(err => {
    return (
        <div key={err.fieldName} className="message message--error">
            <p>{err.errorMessage}</p>
        </div>
    )
})

const Errors = (props) => {
    const errors = Object.keys(props.errorList).map(key => {
        return {
            fieldName: key,
            errorMessage: props.errorList[key]
        }
    })

    return (
        <div>
            {errors.length > 0 ? renderErrors(errors) : null}
        </div>
    )
}

Errors.defaultProps = {
    errorList: {}
}

Errors.propTypes = {
    errorList: PropTypes.object
}

export default Errors

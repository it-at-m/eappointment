import React from 'react'
import PropTypes from 'prop-types'



const renderErrors = errors => errors.map(err => {
    return (
        <div key={err.key} className="message message--error">
            <p>{err.errorMessage}</p>
        </div>
    )
})

const Errors = (props) => {
    const errors = Object.keys(props.errors).map(key => {
        return {
            fieldName: key,
            errorMessage: props.errors[key]
        }
    })

    return (
        <div>
            {errors.length > 0 ? renderErrors(errors) : null}
        </div>
    )
}

Errors.defaultProps = {
    errors: {}
}

Errors.propTypes = {
    errors: PropTypes.object
}

export default Errors

import React from 'react'
import PropTypes from 'prop-types' 
import Errors from './form/errors'
import Conflicts from './form/conflicts'
 
const ErrorBar = (props) => {
    const { errorList, conflictList } = props
    return (
        <div ref={props.setErrorRef} id="errorRef">
            <Errors {...{ errorList }} />
            <Conflicts {...{ conflictList }} />
       </div>
    )
}

ErrorBar.propTypes = {
    setErrorRef: PropTypes.func,
    errorList: PropTypes.object,
    conflictList: PropTypes.object
}

export default ErrorBar

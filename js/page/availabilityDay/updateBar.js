import React, { PropTypes } from 'react'

const UpdateBar = (props) => {

    const onSave = ev => {
        ev.preventDefault()
        props.onSave()
    }

    const onRevert = ev => {
        ev.preventDefault()
        props.onRevert()
    }

    return (
        <div>
            <button onClick={onSave}>Save Changes</button>
            <button onClick={onRevert}>Revert Changes</button>
        </div>
    )
}

UpdateBar.propTypes = {
    onSave: PropTypes.func.isRequried,
    onRevert: PropTypes.func.isRequried
}

export default UpdateBar

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
        <div className="availability-updatebar lineup">
            <div className="lineup_actor lineup_actor--left">
            </div>
            <div className="lineup_actor lineup_actor--right">
                <p>
                    <button className="btn" onClick={onRevert}>Zurücksetzen</button>
                    <button className="btn" onClick={onSave}>Änderungen speichern</button>
                </p>
            </div>
        </div>
    )
}

UpdateBar.propTypes = {
    onSave: PropTypes.func.isRequried,
    onRevert: PropTypes.func.isRequried
}

export default UpdateBar

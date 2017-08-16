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
        <div className="availability-updatebar lineup lineup--availability">
            <div className="lineup_actor lineup_actor--left">
                <p>
                    <button className="btn" onClick={onRevert}>Zurücksetzen</button>
                </p>
            </div>
            <div className="lineup_actor lineup_actor--right">
                <p>
                    <button className="button-save" onClick={onSave}>Alle Änderungen aktivieren</button>
                </p>
            </div>
        </div>
    )
}

UpdateBar.propTypes = {
    onSave: PropTypes.func.isRequired,
    onRevert: PropTypes.func.isRequired
}

export default UpdateBar

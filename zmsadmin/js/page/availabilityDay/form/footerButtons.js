import React from 'react'
import PropTypes from 'prop-types'

const FooterButtons = (props) => {
    const { hasConflicts, stateChanged, data, onNew, onPublish, onAbort, hasSlotCountError } = props
    return (
        <div className="form-actions" style={{"marginTop":"0", "padding":"0.75em"}}>    
                 <button title="Neue Öffnungszeit anlegen und bearbeiten" className="button button--diamond button-new" onClick={onNew} disabled={(stateChanged || hasConflicts || data)}>neue Öffnungszeit</button> 
                <button title="Alle Änderungen werden zurückgesetzt" className="button btn" type="abort" onClick={onAbort} disabled={(!stateChanged && !hasConflicts && !data)}>Abbrechen</button>
                <button title="Alle Änderungen werden gespeichert" className="button button--positive button-save" type="save" value="publish" onClick={onPublish} disabled={(!stateChanged  || hasSlotCountError)}>Alle Änderungen aktivieren
                </button>
             
        </div>
       
    )
}

FooterButtons.propTypes = {
    data: PropTypes.object,
    hasConflicts: PropTypes.bool,
    stateChanged: PropTypes.bool,
    onNew: PropTypes.func,
    onPublish: PropTypes.func,
    onAbort: PropTypes.func
}

export default FooterButtons

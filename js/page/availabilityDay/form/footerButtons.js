import React from 'react'
import PropTypes from 'prop-types'

const FooterButtons = (props) => {
    const { hasConflicts, stateChanged, data, onNew, onPublish, onAbort } = props
    const disabled = (! stateChanged || hasConflicts || (data && ! data.__modified));
    return (
        <div className="form-actions" style={{"marginTop":"0", "padding":"0.75em"}}>    
                 <button title="Neue Öffnungszeit anlegen und bearbeiten" className="button button--diamond button-new" onClick={onNew} disabled={(data && stateChanged && !hasConflicts)}>neue Öffnungszeit</button> 
                <button title="Alle Änderungen werden zurückgesetzt" className="button btn" type="abort" onClick={onAbort}>Abbrechen</button>
                <button title="Alle Änderungen werden gespeichert" className="button button--positive button-save" type="save" value="publish" onClick={onPublish} disabled={disabled}>Alle Änderungen aktivieren
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

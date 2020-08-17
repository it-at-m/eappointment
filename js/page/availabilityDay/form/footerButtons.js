import React from 'react'
import PropTypes from 'prop-types'

const FooterButtons = (props) => {
    const { stateChanged, data, onNew, onPublish, onDelete, onAbort } = props
    return (
        <div className="form-actions" style={{"marginTop":"0", "padding":"0.75em"}}>    
                 {! data ? <button className="button button--diamond button-new" onClick={onNew}>neue Öffnungszeit</button>: null} 
                {data && data.id ? <button className="button button--destructive button-delete" type="delete" value="delete" onClick={onDelete}>Löschen</button>: null} 
                <button className="button btn" type="abort" onClick={onAbort}>Abbrechen</button>
                {(data && (data.__modified || data.tempId)) || stateChanged ? <button className="button button--positive button-save"
                    type="save"
                    value="publish"
                    onClick={onPublish}>Alle Änderungen aktivieren
                </button> : null} 
             
        </div>
       
    )
}

FooterButtons.propTypes = {
    data: PropTypes.object,
    stateChanged: PropTypes.bool,
    onNew: PropTypes.func,
    onPublish: PropTypes.func,
    onDelete: PropTypes.func,
    onAbort: PropTypes.func
}

export default FooterButtons

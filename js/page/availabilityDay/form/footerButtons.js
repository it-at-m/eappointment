import React from 'react'
import PropTypes from 'prop-types'

const FooterButtons = (props) => {
    const { data, onNewClick, onPublish, onDelete, onAbort } = props

    return (
        <div className="form-actions" style={{"marginTop":"0", "padding":"0.75em"}}>    
                 
                <button className="button button--diamond button-new" onClick={onNewClick}>neue Öffnungszeit</button>
                {data ? <button className="button button--destructive button-delete" type="delete" value="delete" onClick={onDelete}>Löschen</button>: null} 
                <button className="button btn" type="abort" onClick={onAbort}>Abbrechen</button>
                {data && data.__modified ? <button className="button button--positive button-save"
                    type="save"
                    value="publish"
                    onClick={onPublish}>Alle Änderungen aktivieren
                </button> : null} 
             
        </div>
       
    )
}

FooterButtons.propTypes = {
    data: PropTypes.object,
    onNewClick: PropTypes.func,
    onPublish: PropTypes.func,
    onDelete: PropTypes.func,
    onAbort: PropTypes.func
}

export default FooterButtons

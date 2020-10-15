import React from 'react'
import PropTypes from 'prop-types'

const FormButtons = (props) => {
    const { data, onCopy, onExclusion, onEditInFuture, onDelete, selectedDate } = props
    const disabled = (data && (! data.id || data.__modified === true));
    return (
        <div>
            <div className="form-actions" style={{"marginTop": "-45px"}}>
                <button onClick={onDelete}
                    title="Ausgewählte Öffnungszeit löschen"
                    className="button button--destructive button-delete" disabled={disabled}>Löschen</button>
                <button onClick={onCopy}
                    title="Öffnungszeit kopieren und bearbeiten"
                    className="button button--diamond" disabled={disabled}>Kopieren</button>
                <button onClick={onExclusion}
                    title="Ausnahme von dieser Öffnungszeit eintragen"
                    className="button button--diamond" disabled={disabled}>Ausnahme</button>
                <button onClick={onEditInFuture}
                    title="Öffnungszeit ab diesem Tag ändern"
                    className="button button--diamond" disabled={disabled || data.startDate == selectedDate}>Ab diesem Tag ändern</button> 
            </div>
        </div>
    )
}

FormButtons.propTypes = {
    data: PropTypes.object,
    onCopy: PropTypes.func,
    onExclusion: PropTypes.func,
    onEditInFuture: PropTypes.func,
    onDelete: PropTypes.func,
    selectedDate: PropTypes.number
}

export default FormButtons

import React from 'react'
import PropTypes from 'prop-types'

const FormButtons = (props) => {
    const { data, onCopy, onExclusion, onEditInFuture } = props

    return (
        <div>
            { data.id ? 
            <div className="form-actions" style={{"marginTop": "-45px"}}>
                <a href="#" onClick={onCopy}
                    title="Öffnungszeit kopieren und bearbeiten"
                    className="button button--diamond">Kopieren</a>
                <a href="#" onClick={onExclusion}
                    title="Ausnahme von dieser Öffnungszeit eintragen"
                    className="button button--diamond">Ausnahme</a>
                <a href="#" onClick={onEditInFuture}
                    title="Öffnungszeit ab diesem Tag ändern"
                    className="button button--diamond">Ab diesem Tag ändern
                </a> 
            </div>
            : null }
        </div>
    )
}

FormButtons.propTypes = {
    data: PropTypes.object,
    onCopy: PropTypes.func,
    onExclusion: PropTypes.func,
    onEditInFuture: PropTypes.func
}

export default FormButtons

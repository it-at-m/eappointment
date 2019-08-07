import React, { PropTypes } from 'react'

const HeaderButtons = (props) => {
    const { onCopy, onException, onEditInFuture } = props

    return (
        <div>
            <a href="#" onClick={onCopy}
                title="Öffnungszeit kopieren und bearbeiten"
                className="button button--diamond"><i className="far fa-copy" aria-hidden="true"></i> Kopieren</a>
            <a href="#" onClick={onException}
                title="Ausnahme von dieser Öffnungszeit eintragen"
                className="button button--diamond"><i className="fas fa-cut" aria-hidden="true"></i>  Ausnahme</a>
            <a href="#" onClick={onEditInFuture}
                title="Öffnungszeit ab diesem Tag ändern"
                className="button button--diamond"><i className="fas fa-unlink" aria-hidden="true"></i> Ab diesem Tag ändern</a>
        </div>
    )
}

HeaderButtons.propTypes = {
    onCopy: PropTypes.func,
    onException: PropTypes.func,
    onEditInFuture: PropTypes.func
}

export default HeaderButtons

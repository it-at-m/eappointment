import React, { PropTypes } from 'react'

const HeaderButtons = (props) => {
    const { onCopy, onException, onEditInFuture } = props

    return (
        <div>
            <a href="#" onClick={onCopy}
                title="Öffnungszeit kopieren und bearbeiten"
                className="btn btn--b3igicon">+ Kopieren</a>
            <a href="#" onClick={onException}
                title="Ausnahme von dieser Öffnungszeit eintragen"
                className="btn btn--b3igicon">  Ausnahme</a>
            <a href="#" onClick={onEditInFuture}
                title="Öffnungszeit ab diesem Tag ändern"
                className="btn btn--b3igicon"> Ab diesem Tag ändern</a>
        </div>
    )
}

HeaderButtons.propTypes = {
    onCopy: PropTypes.func,
    onException: PropTypes.func,
    onEditInFuture: PropTypes.func
}

export default HeaderButtons

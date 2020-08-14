import React from 'react'
import PropTypes from 'prop-types'

const HeaderButtons = (props) => {
    const { onCopy, onException, onEditInFuture } = props

    return (
        <div>
            <a href="#" onClick={onCopy}
                title="Öffnungszeit kopieren und bearbeiten"
                className="button button--diamond">Kopieren</a>
            <a href="#" onClick={onException}
                title="Ausnahme von dieser Öffnungszeit eintragen"
                className="button button--diamond">Ausnahme</a>
            <a href="#" onClick={onEditInFuture}
                title="Öffnungszeit ab diesem Tag ändern"
                className="button button--diamond">Ab diesem Tag ändern</a>
        </div>
    )
}

HeaderButtons.propTypes = {
    onCopy: PropTypes.func,
    onException: PropTypes.func,
    onEditInFuture: PropTypes.func
}

export default HeaderButtons

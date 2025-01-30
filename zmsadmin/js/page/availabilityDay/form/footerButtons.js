import React from 'react'
import PropTypes from 'prop-types'

const FooterButtons = (props) => {
    const { hasConflicts, hasErrors, stateChanged, data, onNew, onPublish, onAbort, hasSlotCountError, availabilitylist } = props;

    const hasNewAvailabilities = availabilitylist?.some(
        availability => availability?.tempId?.includes('__temp__')
    );

    return (
        <div className="form-actions" style={{ "marginTop": "0", "padding": "0.75em" }}>
            <button
                title="Neue Öffnungszeit anlegen und bearbeiten"
                className="button button--diamond button-new"
                onClick={onNew}
                disabled={(stateChanged || data || hasConflicts)}
            >
                neue Öffnungszeit
            </button>
            <button
                title="Alle Änderungen werden zurückgesetzt"
                className="button btn"
                type="abort"
                onClick={onAbort}
                disabled={!stateChanged && !hasNewAvailabilities && !hasConflicts && !hasErrors}
            >
                Abbrechen
            </button>
            <button
                title="Alle Änderungen werden gespeichert"
                className="button button--positive button-save"
                type="save"
                value="publish"
                onClick={onPublish}
                disabled={(!stateChanged && !hasNewAvailabilities) || hasSlotCountError || hasConflicts || hasErrors}
            >
                Alle Änderungen aktivieren
            </button>
        </div>
    )
}

FooterButtons.propTypes = {
    data: PropTypes.object,
    hasConflicts: PropTypes.bool,
    hasErrors: PropTypes.bool,
    stateChanged: PropTypes.bool,
    onNew: PropTypes.func,
    onPublish: PropTypes.func,
    onAbort: PropTypes.func,
    hasSlotCountError: PropTypes.bool,
    availabilitylist: PropTypes.array
}

export default FooterButtons

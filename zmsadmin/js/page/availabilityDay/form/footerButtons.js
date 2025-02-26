import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'

const FooterButtons = (props) => {
    const { 
        hasConflicts, 
        hasErrors, 
        stateChanged, 
        data, 
        onNew, 
        onPublish, 
        onAbort, 
        hasSlotCountError, 
        availabilitylist,
        selectedDate 
    } = props;

    const hasNewAvailabilities = availabilitylist?.some(
        availability => availability?.tempId?.includes('__temp__')
    );

    const hasSplitInProgress = (() => {
        let hasOriginWithId = false;
        let hasExclusion = false;
        let hasFuture = false;

        availabilitylist?.forEach(availability => {
            if (availability?.kind) {
                if (availability.kind === 'origin' && availability.id) {
                    hasOriginWithId = true;
                } else if (availability.kind === 'exclusion') {
                    hasExclusion = true;
                } else if (availability.kind === 'future') {
                    hasFuture = true;
                }
            }
        });

        return hasOriginWithId && (hasExclusion || hasFuture);
    })();

    const isPastDate = moment.unix(selectedDate).isBefore(moment(), 'day');

    return (
        <div className="form-actions" style={{ "marginTop": "0", "padding": "0.75em" }}>
            <button
                title="Neue Öffnungszeit anlegen und bearbeiten"
                className="button button--diamond button-new"
                onClick={onNew}
                disabled={data || hasConflicts || hasSplitInProgress || isPastDate}
            >
                neue Öffnungszeit
            </button>
            <button
                title="Alle Änderungen werden zurückgesetzt"
                className="button btn"
                type="abort"
                onClick={onAbort}
                disabled={(!stateChanged && !hasNewAvailabilities && !hasConflicts && !hasErrors) || isPastDate}
            >
                Abbrechen
            </button>
            <button
                title="Alle Änderungen werden gespeichert"
                className="button button--positive button-save"
                type="save"
                value="publish"
                onClick={onPublish}
                disabled={(!stateChanged && !hasNewAvailabilities) || hasSlotCountError || hasConflicts || hasErrors || isPastDate}
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
    availabilitylist: PropTypes.array,
    selectedDate: PropTypes.number.isRequired
}

export default FooterButtons
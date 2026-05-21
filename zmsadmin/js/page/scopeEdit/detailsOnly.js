import React, { Component } from 'react'
import PropTypes from 'prop-types'
import SourceDetailsView from '../../block/scope/sourcesDetailsForm'

class ScopeDetailsView extends Component {
    render() {
        return (
            <fieldset className="panel--heavy">
                <SourceDetailsView {...this.props} standalone />
            </fieldset>
        )
    }
}

ScopeDetailsView.propTypes = {
    labels: PropTypes.object.isRequired,
    scopestate: PropTypes.object.isRequired,
    permissions: PropTypes.object,
}

export default ScopeDetailsView

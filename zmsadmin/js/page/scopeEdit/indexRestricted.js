import React, { Component } from 'react'
import PropTypes from 'prop-types'
import SourceReadView from '../../block/scope/sourceReadForm'

class ScopeRestrictedView extends Component {
    render() {
        return (
            <fieldset className="panel--heavy">
                <SourceReadView {...this.props} standalone />
            </fieldset>
        )
    }
}

ScopeRestrictedView.propTypes = {
    labels: PropTypes.object.isRequired,
    scopestate: PropTypes.object.isRequired,
    permissions: PropTypes.object,
}

export default ScopeRestrictedView

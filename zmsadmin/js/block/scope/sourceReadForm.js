import React, { Component } from 'react'
import PropTypes from 'prop-types'
import * as Inputs from '../../lib/inputs'

class SourceReadView extends Component {
    render() {
        const hasProvider = () => {
            return (this.props.scopestate.provider && this.props.scopestate.provider.id) ? true : false;
        }

        const hasSource = () => {
            return (this.props.scopestate.source && this.props.scopestate.source.source) ? true : false;
        }

        const permissions = this.props.permissions || {}

        return (
            <>
                <Inputs.Hidden
                    name="id"
                    value={(this.props.scopestate.scope) ? this.props.scopestate.scope.id : 0}
                />
                {this.props.standalone && !permissions.scope && (
                    <>
                        <Inputs.Hidden
                            name="provider[source]"
                            value={(hasSource()) ? this.props.scopestate.source.source : ''}
                        />
                        <Inputs.Hidden
                            name="provider[id]"
                            value={(hasProvider()) ? this.props.scopestate.provider.id : ''}
                        />
                    </>
                )}
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            value={this.props.labels.name}
                            attributes={{ "htmlFor": "scopeProviderName" }}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                attributes={{ "id": "scopeProviderName", "readOnly": true, "maxLength": 40 }}
                                name="contact[name]"
                                value={(hasProvider()) ? this.props.scopestate.provider.contact.name : this.props.labels.notDeclared}
                            />
                        </Inputs.Controls>
                        <Inputs.Hidden
                            name="provider[name]"
                            value={(hasProvider()) ? this.props.scopestate.provider.name : this.props.labels.notDeclared}
                        />
                    </Inputs.FormGroup>
                </div>
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            value={this.props.labels.address}
                            attributes={{ "htmlFor": "scopeProviderStreet" }}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                attributes={{ "id": "scopeProviderStreet", "readOnly": true, "maxLength": 70 }}
                                name="contact[street]"
                                value={(hasProvider()) ? this.props.scopestate.provider.contact.street + " " + this.props.scopestate.provider.contact.streetNumber : this.props.labels.notDeclared}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
            </>
        )
    }
}

SourceReadView.propTypes = {
    labels: PropTypes.object.isRequired,
    scopestate: PropTypes.object.isRequired,
    permissions: PropTypes.object,
    standalone: PropTypes.bool,
}

export default SourceReadView

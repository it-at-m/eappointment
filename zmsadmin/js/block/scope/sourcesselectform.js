import React, { Component } from 'react'
import PropTypes from 'prop-types'
import * as Inputs from '../../lib/inputs'
import { loopWithCallback } from '../../lib/utils'
import { sortByName } from '../../lib/sort'

class SourceSelectView extends Component {
    constructor(props) {
        super(props)
    }

    componentDidUpdate() {
        //console.log("updated component", this.props.scopestate.provider)
    }

    render() {
        const providerList = Object.values(this.props.scopestate.providerList)

        const sourceList = Object.values(this.props.scopestate.sourcelist)

        const sourceOptions = [{
            "value": 0,
            "name": this.props.labels.selectPlease
        }].concat(sourceList.map((item) => {
            return {
                name: item.label, value: item.source
            }
        })).sort(sortByName)

        const providerGroups = [{
            'label': this.props.labels.selectPlease,
            'options': [
                { "value": 0, "name": this.props.labels.selectPlease }
            ]
        }].concat(providerList.map((group) => {
            return {
                label: this.props.labels[group.name],
                options: Object.values(group.items).map(item => {
                    return {
                        name: item.name, value: item.id
                    }
                }).sort(sortByName)
            }
        }))

        const onChangeProvider = (field, selectedProviderId) => {
            this.props.changeHandler('provider', loopWithCallback(providerList, (group) => {
                if (selectedProviderId != 0) {
                    return Object.values(group.items).find(provider => provider.id === selectedProviderId)
                }
                return Object.values(group.items).find(provider => provider.id === this.props.scopestate.provider.id)
            }))
        }

        const onChangeSource = (field, selectedSource) => {
            if (selectedSource != 0) {
                this.props.onChangeSourceHandler('providerList', selectedSource)
                this.props.changeHandler('source', sourceList.find(source => source.source === selectedSource))
            }
        }

        const hasProvider = () => {
            return (this.props.scopestate.provider && this.props.scopestate.provider.id) ? true : false;
        }

        const hasSource = () => {
            return (this.props.scopestate.source && this.props.scopestate.source.source) ? true : false;
        }

        return (
            <fieldset className="panel--heavy">
                <div>
                    <Inputs.Hidden
                        name="id"
                        value={(this.props.scopestate.scope) ? this.props.scopestate.scope.id : 0}
                    />
                    <Inputs.FormGroup>
                        <Inputs.Label
                            value={this.props.labels.sources}
                            attributes={{ "htmlFor": "scopeProviderSource" }}
                        />
                        <Inputs.Controls>
                            <Inputs.Select
                                value={(hasSource()) ? this.props.scopestate.source.source : 0}
                                name="provider[source]"
                                options={sourceOptions}
                                onChange={onChangeSource}
                                attributes={{ "id": "scopeProviderSource" }}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
                <div>

                    <Inputs.FormGroup>
                        <Inputs.Label
                            value={this.props.labels.providers}
                            attributes={{ "htmlFor": "scopeProviderId" }}
                        />
                        <Inputs.Controls>
                            <Inputs.Select
                                attributes={(!hasSource()) ? { "disabled": true, "id": "scopeProviderId" } : { "id": "scopeProviderId" }}
                                value={(hasProvider()) ? this.props.scopestate.provider.id : 0}
                                name="provider[id]"
                                groups={providerGroups}
                                onChange={onChangeProvider}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
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
            </fieldset>
        )
    }
}

SourceSelectView.propTypes = {
    labels: PropTypes.object.isRequired,
    scopestate: PropTypes.object.isRequired,
    changeHandler: PropTypes.func,
    onChangeSourceHandler: PropTypes.func,
}

export default SourceSelectView

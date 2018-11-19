import React, { Component, PropTypes } from 'react'
import { toArray } from '../../lib/utils'
import * as Inputs from '../../lib/inputs'

class SourceSelectView extends Component {
    constructor(props) {
        super(props)
    }

    componentDidUpdate() {
        console.log("updated component", this.props.scopestate.provider)
    }

    render() {
        const providerList = Object.values(this.props.scopestate.providerList)
        const sourceList = Object.values(this.props.scopestate.sourcelist)
        const providerGroups = providerList.map((group) => {
            return {
                label: this.props.labels[group.name],
                options: Object.values(group.items).map(item => {
                    return {
                        name: item.name, value: item.id
                    }
                })
            }
        });

        const onChangeProvider = (field, selectedProviderId) => {
            this.props.changeHandler('provider', providerList.reduce((group) => {
                if (selectedProviderId) {
                    return Object.values(group.items).find(provider => provider.id === selectedProviderId)
                }
                return Object.values(group.items).find(provider => provider.id === this.props.scopestate.provider.id)
            }))
        }

        const onChangeSource = (field, selectedSource) => {
            if (selectedSource) {
                this.props.onChangeSourceHandler('providerList', selectedSource)
                this.props.changeHandler('source', sourceList.find(source => source.source === selectedSource))
            } else {
                this.props.onChangeSourceHandler('providerList', this.props.scopestate.source.source)
                this.props.changeHandler('source', sourceList.find(source => source.source === source.source))
            }
        }

        return (
            <fieldset>
                <div>
                    <Inputs.Hidden
                        name="id"
                        value={(this.props.scopestate.scope) ? this.props.scopestate.scope.id : 0}
                    />
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.props.labels.sources}
                        />
                        <Inputs.Controls>
                            <Inputs.Select
                                value={this.props.scopestate.source.source}
                                name="provider[source]"
                                options={
                                    sourceList.map((item) => {
                                        return {
                                            name: item.label, value: item.source
                                        }
                                    })
                                }
                                onChange={onChangeSource}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
                <div>

                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.props.labels.providers}
                        />
                        <Inputs.Controls>
                            <Inputs.Select
                                value={this.props.scopestate.provider.id}
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
                            children={this.props.labels.name}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                attributes={{ "readOnly": true, "maxLength": 40 }}
                                name="contact[name]"
                                value={(this.props.scopestate.provider.contact) ? this.props.scopestate.provider.contact.name : ''}
                            />
                        </Inputs.Controls>
                        <Inputs.Hidden
                            name="provider[name]"
                            value={(this.props.scopestate.provider) ? this.props.scopestate.provider.name : ''}
                        />
                    </Inputs.FormGroup>
                </div>
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.props.labels.address}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                attributes={{ "readOnly": true, "maxLength": 70 }}
                                name="contact[street]"
                                value={(this.props.scopestate.provider.contact) ? this.props.scopestate.provider.contact.street + " " + this.props.scopestate.provider.contact.streetNumber : ''}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
            </fieldset>
        )
    }
}

SourceSelectView.propTypes = {
    labels: PropTypes.array.isRequired,
    scopestate: PropTypes.array.isRequired,
    changeHandler: PropTypes.handler,
    onChangeSourceHandler: PropTypes.handler,
    descriptions: PropTypes.array,
    includeUrl: PropTypes.string.isRequired
}

export default SourceSelectView

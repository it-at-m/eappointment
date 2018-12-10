import React, { Component, PropTypes } from 'react'
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

        const providerExists = () => {
            return (this.props.scopestate.provider && this.props.scopestate.provider.id) ? true : false;
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
                                options={sourceOptions}
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
                                value={(providerExists()) ? this.props.scopestate.provider.id : 0}
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
                                value={(providerExists()) ? this.props.scopestate.provider.contact.name : this.props.labels.notDeclared}
                            />
                        </Inputs.Controls>
                        <Inputs.Hidden
                            name="provider[name]"
                            value={(providerExists()) ? this.props.scopestate.provider.name : this.props.labels.notDeclared}
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
                                value={(providerExists()) ? this.props.scopestate.provider.contact.street + " " + this.props.scopestate.provider.contact.streetNumber : this.props.labels.notDeclared}
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
    descriptions: PropTypes.array
}

export default SourceSelectView

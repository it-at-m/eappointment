import React, { Component } from 'react'
import PropTypes from 'prop-types'
import $ from "jquery"
import * as Inputs from '../../lib/inputs'
import { getEntity } from '../../lib/schema'

const renderProvider = (provider, index, onChange, onDeleteClick, labels, descriptions, source) => {
    const formName = `providers[${index}]`

    return (
        <tr key={index} className="provider-item">
            <td className="provider-item__id" width="auto">
                <Inputs.Text
                    name={`${formName}[id]`}
                    placeholder={labels.id}
                    value={provider.id}
                    attributes={{ "readOnly": "1", "aria-label": "Laufende Nummer" }}
                />
            </td>
            <td className="provider-item__name" width="auto">
                <Inputs.Text
                    name={`${formName}[name]`}
                    placeholder={labels.name}
                    value={provider.name}
                    onChange={onChange}
                    attributes={{ "aria-label": "Bezeichnung" }}
                />
            </td>
            <td className="provider-item__link">
                <Inputs.FormGroup>
                    <Inputs.Label
                        value={`${labels.url}`}
                        attributes={{ "htmlFor": `providersUrl${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[link]`}
                            placeholder={labels.url}
                            value={provider.link}
                            onChange={onChange}
                            attributes={{ "id": `providersUrl${index}` }}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        value={labels.street}
                        attributes={{ "htmlFor": `providersStreet${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[contact][street]`}
                            placeholder={labels.street}
                            value={provider.contact.street}
                            onChange={onChange}
                            attributes={{ "id": `providersStreet${index}` }}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        value={labels.streetNumber}
                        attributes={{ "htmlFor": `providersStreetnumber${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[contact][streetNumber]`}
                            placeholder={labels.streetNumber}
                            value={provider.contact.streetNumber}
                            onChange={onChange}
                            attributes={{ "id": `providersStreetnumber${index}` }}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        value={labels.postalCode}
                        attributes={{ "htmlFor": `providersPostalcode${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[contact][postalCode]`}
                            placeholder={labels.postalCode}
                            value={provider.contact.postalCode}
                            onChange={onChange}
                            attributes={{ "id": `providersPostalcode${index}` }}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        value={labels.city}
                        attributes={{ "htmlFor": `providersCity${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[contact][city]`}
                            placeholder={labels.city}
                            value={provider.contact.city}
                            onChange={onChange}
                            attributes={{ "id": `providersCity${index}` }}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        value={labels.data}
                        attributes={{ "htmlFor": `providersData${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Textarea
                            name={`${formName}[data]`}
                            value={(provider.data) ? JSON.stringify(provider.data) : ''}
                            placeholder='{}'
                            onChange={onChange}
                            attributes={{ "id": `providersData${index}`, "aria-describedby": `help_providersData${index}` }}
                        />
                        <Inputs.Description
                            value={descriptions.data}
                            attributes={{ "id": `help_providersData${index}` }}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.Hidden
                    name={`${formName}[source]`}
                    value={source}
                />
            </td>
            <td className="provider-item__delete">
                <div className="form-check">
                    <label className="checkboxdeselect provider__delete-button form-check-label">
                        <input className="form-check-input" type="checkbox" readOnly={true} checked={true} onClick={() => onDeleteClick(index)} role="button" aria-label="Diesen Datensatz löschen" />
                        <span>Löschen</span>
                    </label>
                </div>
            </td>
        </tr >
    )
}

class ProvidersView extends Component {
    constructor(props) {
        super(props)
    }

    getNextId() {
        let nextId = Number(this.props.source.providers.length ? this.props.source.providers[this.props.source.providers.length - 1].id : 0) + 1
        return nextId;
    }

    getProvidersWithLabels(onChange, onDeleteClick) {
        return this.props.source.providers.map((provider, index) => renderProvider(provider, index, onChange, onDeleteClick, this.props.labelsproviders, this.props.descriptions, this.props.source.source))
    }

    hideDeleteButton() {
        $('.provider-item').each((index, item) => {
            if ($(item).find('.provider-item__id input').val()) {
                $(item).find('.provider__delete-button').css("visibility", "hidden");
            }
        })
    }

    componentDidMount() {
        console.log("mounted provider component")
        this.hideDeleteButton()
    }

    componentDidUpdate() {
        //console.log("updated provider component")
    }

    render() {
        const onNewClick = ev => {
            ev.preventDefault()
            getEntity('provider').then((entity) => {
                entity.id = this.getNextId()
                entity.source = this.props.source.source
                this.props.addNewHandler('providers', [entity])
            })
        }

        const onDeleteClick = index => {
            this.props.deleteHandler('providers', index)
        }

        const onChange = (field, value) => {
            this.props.changeHandler(field, value)
        }

        return (
            <div className="table-responsive-wrapper department-providers__list" aria-live="polite" id="liveregionProvidersList">
                <table className="table--base">
                    <thead>
                        <tr>
                            <th>LfdNr.</th>
                            <th>Bezeichnung</th>
                            <th>Link und weitere Daten</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {this.getProvidersWithLabels(onChange, onDeleteClick)}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colSpan="4">
                                <p>
                                    <Inputs.Description
                                        value={this.props.descriptions.delete}
                                    />
                                </p>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div className="table-actions">
                    <button className="link button-default" onClick={onNewClick}><i className="fas fa-plus-square color-positive"></i> Neuer Dienstleister</button>
                </div>
            </div>
        )
    }
}

ProvidersView.propTypes = {
    labelsproviders: PropTypes.object.isRequired,
    descriptions: PropTypes.object.isRequired,
    source: PropTypes.object.isRequired,
    changeHandler: PropTypes.func,
    addNewHandler: PropTypes.func,
    deleteHandler: PropTypes.func
}

export default ProvidersView

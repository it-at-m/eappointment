import React, { Component, PropTypes } from 'react'

import * as Inputs from '../../../lib/inputs'

const renderProvider = (provider, index, onChange, labels, descriptions) => {
    const formName = `providers[${index}]`

    const onChangeName = (_, value) => onChange(index, 'name', value)
    const onChangeLink = (_, value) => onChange(index, 'link', value)
    const onChangeStreet = (_, value) => onChange(index, 'street', value)
    const onChangeStreetNumber = (_, value) => onChange(index, 'streetNumber', value)
    const onChangePostalCode = (_, value) => onChange(index, 'postalCode', value)
    const onChangeCity = (_, value) => onChange(index, 'city', value)
    const onChangeData = (_, value) => onChange(index, 'data', value)

    return (
        <tr className="provider-item">
            <td className="provider-item__id" width="12%">
                <Inputs.Text
                    name={`${formName}[id]`}
                    placeholder={labels.id}
                    value={provider.id}
                    attributes={{ "readOnly": "1" }}
                />
            </td>
            <td className="provider-item__name" width="28%">
                <Inputs.Text
                    name={`${formName}[name]`}
                    placeholder={labels.name}
                    value={provider.name}
                    onChange={onChangeName}
                />
            </td>
            <td className="provider-item__link">
                <Inputs.FormGroup>
                    <Inputs.Label
                        children={`${labels.url}`}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[link]`}
                            placeholder={labels.url}
                            value={provider.link}
                            onChange={onChangeLink}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        children={labels.street}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[street]`}
                            placeholder={labels.street}
                            value={provider.contact.street}
                            onChange={onChangeStreet}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        children={labels.streetNumber}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[streetNumber]`}
                            placeholder={labels.streetNumber}
                            value={provider.contact.streetNumber}
                            onChange={onChangeStreetNumber}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        children={labels.postalCode}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[postalCode]`}
                            placeholder={labels.postalCode}
                            value={provider.contact.postalCode}
                            onChange={onChangePostalCode}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        children={labels.city}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[city]`}
                            placeholder={labels.city}
                            value={provider.contact.city}
                            onChange={onChangeCity}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        children={labels.data}
                    />
                    <Inputs.Controls>
                        <Inputs.Textarea
                            name={`${formName}[data]`}
                            value={JSON.stringify(provider.data)}
                            placeholder="{}"
                            onChange={onChangeData}
                        />
                        <Inputs.Description
                            children={descriptions.data}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.Hidden
                    name={`${formName}[source]`}
                    value={provider.source}
                />
            </td>
        </tr >
    )
}

class ProvidersView extends Component {
    constructor(props) {
        super(props)
        this.state = {
            providers: props.providers.length > 0
                ? props.providers
                : [{
                    source: '',
                    id: '',
                    name: '',
                    link: '',
                    contact: {
                        street: '',
                        streetNumber: '',
                        postalCode: '',
                        city: '',
                    },
                    data: '{}'
                }],
            labels: props.labels,
            descriptions: props.descriptions
        }
    }

    changeItemField(index, field, value) {
        //console.log('change item field', index, field, value)
        this.setState({
            providers: this.state.providers.map((provider, requestIndex) => {
                return index === requestIndex ? Object.assign({}, provider, { [field]: value }) : provider
            })
        })
    }

    addNewItem() {
        this.setState({
            providers: this.state.providers.concat([{
                source: '',
                id: '',
                name: '',
                link: '',
                contact: {
                    street: '',
                    streetNumber: '',
                    postalCode: '',
                    city: '',
                },
                data: {}
            }])
        })
    }

    deleteItem(deleteIndex) {
        this.setState({
            providers: this.state.providers.filter((provider, index) => {
                return index !== deleteIndex
            })
        })
    }

    getProvidersWithLabels(onChange) {
        return this.state.providers.map((provider, index) => renderProvider(provider, index, onChange, this.state.labels, this.state.descriptions))
    }

    render() {
        //console.log('providersView::render', this.state)

        const onNewClick = ev => {
            ev.preventDefault()
            this.addNewItem()
        }

        const onChange = (index, field, value) => {
            this.changeItemField(index, field, value)
        }

        return (
            <div className="department-providers__list">
                <table className="clean">
                    <thead>
                        <th>LfdNr.</th>
                        <th>Bezeichnung</th>
                        <th>Link und weitere Daten</th>
                    </thead>
                    <tbody>
                        {this.getProvidersWithLabels(onChange)}
                        <tr>
                            <td colSpan="4">
                                <button className="button-default" onClick={onNewClick}>Neuer Dienstleister</button>
                                <Inputs.Description
                                    children={this.state.descriptions.delete}
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        )
    }
}

ProvidersView.propTypes = {
    providers: PropTypes.array,
    labels: PropTypes.array.isRequired,
    descriptions: PropTypes.array.isRequired,
}

export default ProvidersView

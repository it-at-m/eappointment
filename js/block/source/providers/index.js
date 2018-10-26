import React, { Component, PropTypes } from 'react'

import * as Inputs from '../../../lib/inputs'

const renderProvider = (provider, index, onChange, onDeleteClick, labels, descriptions, source) => {
    const formName = `providers[${index}]`


    const onChangeName = (_, value) => onChange(index, 'name', value)
    const onChangeLink = (_, value) => onChange(index, 'link', value)
    const onChangeStreet = (_, value) => onChange(index, 'contact[street]', value)
    const onChangeStreetNumber = (_, value) => onChange(index, '[contact][streetNumber]', value)
    const onChangePostalCode = (_, value) => onChange(index, '[contact][postalCode]', value)
    const onChangeCity = (_, value) => onChange(index, '[contact][city]', value)
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
                            name={`${formName}[contact][street]`}
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
                            name={`${formName}[contact][streetNumber]`}
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
                            name={`${formName}[contact][postalCode]`}
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
                            name={`${formName}[contact][city]`}
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
                            value={(provider.data) ? JSON.stringify(provider.data) : ''}
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
                    value={source}
                />
            </td>
            <td className="provider-item__delete">
                <label className="checkboxdeselect provider__delete-button">
                    <input type="checkbox" checked={true} onClick={() => onDeleteClick(index)} /><span></span>
                </label>
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
                    source: props.source.source ? props.source.source : '',
                    id: 1,
                    name: '',
                    link: '',
                    contact: {
                        street: '',
                        streetNumber: '',
                        postalCode: '',
                        city: '',
                    },
                    data: ''
                }],
            labels: props.labelsproviders,
            descriptions: props.descriptions,
            source: props.source
        }
    }

    changeItemField(index, field, value) {
        let newstate = { [field]: value };
        if (field.match(/.\[/)) {
            const firstPart = field.split('[')[0];
            const secondPart = field.split('[')[1].replace(']', '');
            newstate = { [firstPart]: { [secondPart]: value } };
        }
        console.log('change item field', index, field, value, newstate)

        this.setState({
            providers: this.state.providers.map((provider, requestIndex) => {
                return index === requestIndex ? Object.assign({}, provider, newstate) : provider
            })
        })
    }

    getNextId() {
        let nextId = Number(this.state.providers[this.state.providers.length - 1].id) + 1
        return nextId;
        //return this.state.requests.pop().id;
    }

    addNewItem() {
        this.setState({
            providers: this.state.providers.concat([{
                source: this.state.source.source,
                id: this.getNextId(),
                name: '',
                link: '',
                contact: {
                    street: '',
                    streetNumber: '',
                    postalCode: '',
                    city: '',
                },
                data: ''
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

    getProvidersWithLabels(onChange, onDeleteClick) {
        return this.state.providers.map((provider, index) => renderProvider(provider, index, onChange, onDeleteClick, this.state.labels, this.state.descriptions, this.state.source.source))
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
        console.log("updated provider component")
    }

    componentWillReceiveProps(nextProps) {
        // You don't have to do this check first, but it can help prevent an unneeded render
        if (nextProps.source.source !== this.state.source) {
            this.setState({ source: nextProps.source })
            this.render()
        }
    }

    render() {
        //console.log('providersView::render', this.state)

        const onNewClick = ev => {
            ev.preventDefault()
            this.addNewItem()
        }

        const onDeleteClick = index => {
            this.deleteItem(index)
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
                        {this.getProvidersWithLabels(onChange, onDeleteClick)}
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
    labelsproviders: PropTypes.array.isRequired,
    descriptions: PropTypes.array.isRequired,
    source: PropTypes.array.isRequired,
}

export default ProvidersView

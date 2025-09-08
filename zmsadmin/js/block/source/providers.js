import React, { Component } from 'react'
import PropTypes from 'prop-types'
import * as Inputs from '../../lib/inputs'
import { getEntity } from '../../lib/schema'

const renderProvider = (provider, index, onChange, onDeleteClick, labels, descriptions, source, parentProviders, onParentChange, canDelete) => {
    const formName = `providers[${index}]`
    const parentValue = provider.parent_id == null ? '' : String(provider.parent_id);
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
            <td className="provider-item__parent" width="auto">
                <Inputs.Select
                    name={provider.parent_id == null ? undefined : `${formName}[parent_id]`}
                    value={parentValue}
                    onChange={(_, value) => onParentChange(index, value)}
                    options={[
                        { name: '—', value: '' },
                        ...parentProviders.map(p => ({ name: p.name, value: String(p.id) }))
                    ]}
                    attributes={{ "aria-label": labels.parent }}
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
                            key={`prov-data-${index}-${provider.parent_id ?? 'none'}`}
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
            <td className="provider-item__delete" style={{ verticalAlign: 'middle' }}>
                {canDelete && (
                    <button type="button"
                            className="link button-default provider__delete-button"
                            onClick={() => onDeleteClick(index)}
                            aria-label="Diesen Datensatz löschen"
                            style={{ display: 'inline-flex', alignItems: 'center' }}>
                        <i className="fas fa-trash-alt color-negative" style={{ marginRight: '5px' }}></i>
                        Löschen
                    </button>
                )}
            </td>
        </tr >
    )
}

class ProvidersView extends Component {
    constructor(props) {
        super(props)
    }

    getNextId() {
        const list = (this.props.source.providers || []);
        const lastId = list.length ? Number(list[list.length - 1].id) : 0;
        return lastId + 1;
    }

    onParentChange = (rowIndex, rawValue) => {
        const parent_id = rawValue === '' ? null : Number(rawValue);
        const parent = parent_id == null
            ? null
            : this.props.parentproviders.find(p => Number(p.id) === parent_id);

        this.props.changeHandler(`providers[${rowIndex}][parent_id]`, parent_id);
        this.props.changeHandler(`providers[${rowIndex}][link]`, parent?.link || '');
        this.props.changeHandler(`providers[${rowIndex}][contact]`, parent?.contact ? { ...parent.contact } : { street:'', streetNumber:'', postalCode:'', city:'' });
        this.props.changeHandler(`providers[${rowIndex}][data]`, parent?.data || {});
    };

    getProvidersWithLabels(onChange, onDeleteClick) {
        const list = (this.props.source && this.props.source.providers) || [];
        const moreThanOne = list.length > 1;
        return list.map((provider, index) => {
            const canDelete = moreThanOne && (provider.canDelete !== false);
            return renderProvider(
                provider, index, onChange, onDeleteClick,
                this.props.labelsproviders, this.props.descriptions,
                this.props.source.source, this.props.parentproviders,
                this.onParentChange,
                canDelete
            );
        });
    }

    componentDidMount() {}

    componentDidUpdate() {}

    render() {
        const onNewClick = ev => {
            ev.preventDefault()
            getEntity('provider').then((entity) => {
                entity.id = this.getNextId()
                entity.source = this.props.source.source
                entity.__isNew = true
                this.props.addNewHandler('providers', [entity])
            })
        }

        const onDeleteClick = index => {
            const item = this.props.source.providers[index];
            const name = item?.name || 'diesen Datensatz';
            if (item?.__isNew === true) {
                return this.props.deleteHandler('providers', index);
            }
            const msg = `„${name}“ wirklich löschen?\n\nHinweis: Die Änderung wird erst nach „Speichern“ wirksam.`;
            if (window.confirm(msg)) {
                this.props.deleteHandler('providers', index);
            }
        };


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
                            <th>Hauptdienstleister</th>
                            <th>Link und weitere Daten</th>
                            <th>Löschen</th>
                        </tr>
                    </thead>
                    <tbody>
                        {this.getProvidersWithLabels(onChange, onDeleteClick)}
                    </tbody>
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
    deleteHandler: PropTypes.func,
    parentproviders: PropTypes.array.isRequired
}

export default ProvidersView

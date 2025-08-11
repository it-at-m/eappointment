import React, { Component } from 'react'
import PropTypes from 'prop-types'
import * as Inputs from '../../lib/inputs'
import { getEntity } from '../../lib/schema'

const renderRequest = (request, index, onChange, onDeleteClick, labels, descriptions, source, parentRequests, onParentChange, canDelete, requestVariants = []) => {
    const formName = `requests[${index}]`
    const parentValue = request.parent_id == null ? '' : String(request.parent_id);
    return (
        <tr key={index} className="request-item">
            <td className="request-item__id" width="auto">
                <Inputs.Text
                    name={`${formName}[id]`}
                    placeholder={labels.id}
                    value={request.id}
                    attributes={{ "readOnly": "1", "aria-label": "Laufende Nummer" }}
                />
            </td>
            <td className="request-item__name" width="auto">
                <Inputs.Text
                    name={`${formName}[name]`}
                    placeholder={labels.name}
                    value={request.name}
                    onChange={onChange}
                    attributes={{ "aria-label": "Bezeichnung" }}
                />
            </td>
            <td className="request-item__parent" width="auto">
                <Inputs.Select
                    name={request.parent_id == null ? undefined : `${formName}[parent_id]`}
                    value={parentValue}
                    onChange={(_, v) => onParentChange(index, v)}
                    options={[
                        { name: '—', value: '' },
                        ...parentRequests.map(r => ({ name: r.name, value: String(r.id) }))
                    ]}
                    attributes={{ "aria-label": labels.parent }}
                />
            </td>
            <td className="request-item__link">
                <Inputs.FormGroup>
                    <Inputs.Label
                        value={`${labels.url}`}
                        attributes={{ "htmlFor": `requestUrl${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[link]`}
                            placeholder={labels.url}
                            value={request.link}
                            onChange={onChange}
                            attributes={{ "id": `requestUrl${index}` }}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        value={labels.group}
                        attributes={{ "htmlFor": `requestGroup${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[group]`}
                            placeholder={labels.group}
                            value={request.group}
                            onChange={onChange}
                            attributes={{ "id": `requestGroup${index}` }}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        value={labels.data}
                        attributes={{ "htmlFor": `requestData${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Textarea
                            name={`${formName}[data]`}
                            value={(request.data) ? JSON.stringify(request.data) : ''}
                            placeholder='{}'
                            onChange={onChange}
                            attributes={{ "id": `requestData${index}`, "aria-describedby": `help_requestData${index}` }}
                        />
                        <Inputs.Description
                            value={descriptions.data}
                            attributes={{ "id": `help_requestData${index}` }}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.Hidden
                    name={`${formName}[source]`}
                    value={source}
                />
            </td>
            <td>
                <Inputs.FormGroup>
                    <Inputs.Label
                        value={labels.variant}
                        attributes={{ "htmlFor": `requestVariant${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Select
                            name={`${formName}[variant_id]`}
                            value={request.variant_id == null ? '' : String(request.variant_id)}
                            onChange={(_, v) => onChange(`${formName}[variant_id]`, v ? Number(v) : null)}
                            options={[
                                { name: '—', value: '' },
                                ...requestVariants.map(v => ({ name: v.name, value: String(v.id) }))
                            ]}
                            attributes={{ "id": `requestVariant${index}` }}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
            </td>
            <td className="request-item__delete" style={{verticalAlign:'middle'}}>
                {canDelete && (
                    <button type="button"
                            className="link button-default request__delete-button"
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

class RequestsView extends Component {
    constructor(props) {
        super(props)
    }

    getNextId() {
        let nextId = Number(this.props.source.requests.length ? this.props.source.requests[this.props.source.requests.length - 1].id : 0) + 1
        return nextId;
    }

    onParentChange = (rowIndex, rawValue) => {
        const parent_id = rawValue === '' ? null : Number(rawValue);

        const parent = parent_id === null
            ? null
            : this.props.parentrequests.find(r => Number(r.id) === parent_id);

        const requestCopy = {
            ...this.props.source.requests[rowIndex],
            parent_id
        };

        if (parent) {
            requestCopy.link  = parent.link  || '';
            requestCopy.group = parent.group || '';
            requestCopy.data  = parent.data  || '';
        } else {
            requestCopy.link  = '';
            requestCopy.group = '';
            requestCopy.data  = '';
        }

        this.props.changeHandler(`requests[${rowIndex}]`, requestCopy);
    };

    getRequestsWithLabels(onChange, onDeleteClick) {
        const list = (this.props.source.requests || []);
        const moreThanOne = list.length > 1;

        return list.map((request, idx) => {
            const canDelete = moreThanOne && (request.canDelete !== false);
            return renderRequest(
                request, idx, onChange, onDeleteClick,
                this.props.labelsrequests, this.props.descriptions,
                this.props.source.source, this.props.parentrequests,
                this.onParentChange,
                canDelete,
                (this.props.requestvariants || [])
            );
        });
    }

    componentDidMount() {
        console.log("mounted request component")
    }

    componentDidUpdate() {
        //console.log("updated request component")
    }

    render() {
        const onNewClick = ev => {
            ev.preventDefault()
            getEntity('request').then((entity) => {
                entity.id = this.getNextId()
                entity.source = this.props.source.source
                this.props.addNewHandler('requests', [entity])
            })
        }

        const onDeleteClick = index => {
            this.props.deleteHandler('requests', index)
        }

        const onChange = (field, value) => {
            this.props.changeHandler(field, value)
        }

        return (
            <div className="table-responsive-wrapper requests__list" aria-live="polite" id="liveregionRequestList">
                <table className="table--base">
                    <thead>
                        <tr>
                            <th>LfdNr.</th>
                            <th>Bezeichnung</th>
                            <th>Hauptdienstleistung</th>
                            <th>Link und weitere Daten</th>
                            <th>Variante</th>
                            <th>Löschen</th>
                        </tr>
                    </thead>
                    <tbody>
                        {this.getRequestsWithLabels(onChange, onDeleteClick)}
                    </tbody>
                </table>
                <div className="table-actions">
                    <button className="link button-default" onClick={onNewClick}><i className="fas fa-plus-square color-positive"></i> Neue Dienstleistung</button>
                </div>
            </div>
        )
    }
}

RequestsView.propTypes = {
    labelsrequests: PropTypes.object.isRequired,
    descriptions: PropTypes.object.isRequired,
    source: PropTypes.object.isRequired,
    changeHandler: PropTypes.func,
    addNewHandler: PropTypes.func,
    deleteHandler: PropTypes.func,
    parentrequests: PropTypes.array.isRequired,
    requestvariants: PropTypes.array
}

export default RequestsView

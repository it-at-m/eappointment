import React, { Component } from 'react'
import PropTypes from 'prop-types'
import * as Inputs from '../../lib/inputs'
import { getEntity } from '../../lib/schema'

class RequestRelationView extends Component {
    constructor(props) {
        super(props)
    }

    setSlots = (rowIndex, next) => {
        const cur = Number(this.props.source.requestrelation[rowIndex]?.slots);
        if (!Number.isFinite(next)){
            return;
        }
        if (cur !== next) {
            this.props.changeHandler(`requestrelation[${rowIndex}][slots]`, next);
        }
    }

    autofillSlots = (rowIndex, changedField, changedValue) => {
        const rel = this.props.source.requestrelation[rowIndex] || {};

        const reqLocalId = changedField?.endsWith('[request][id]')  ? changedValue : rel.request?.id;
        const prvLocalId = changedField?.endsWith('[provider][id]') ? changedValue : rel.provider?.id;
        if (!reqLocalId || !prvLocalId) return;

        const req = this.props.source.requests.find(r => String(r.id) === String(reqLocalId));
        const prv = this.props.source.providers.find(p => String(p.id) === String(prvLocalId));
        if (!req || !prv) return;

        if (req.parent_id == null || prv.parent_id == null) {
            this.setSlots(rowIndex, 1);
            return;
        }

        const externalServiceId = String(req.parent_id);
        const services = Array.isArray(prv?.data?.services) ? prv.data.services : [];
        const svc = services.find(s => String(s.service) === externalServiceId);

        if (!svc) {
            this.setSlots(rowIndex, 1);
            return;
        }

        const duration = Number(svc.duration);
        const slotSize = Number(prv?.data?.slotTimeInMinutes);
        if (!Number.isFinite(duration) || !Number.isFinite(slotSize) || slotSize <= 0) {
            this.setSlots(rowIndex, 1);
            return;
        }

        const slots = Math.max(1, Math.ceil(duration / slotSize));
        this.setSlots(rowIndex, slots);
    }

    autofillAllRows = () => {
        const list = this.props.source.requestrelation || [];
        list.forEach((rel, idx) => {
            if (rel?.__isNew === true) {
                this.autofillSlots(idx);
            }
        });
    }

    componentDidUpdate() {
        this.autofillAllRows();
    }

    getRequestRelation(onChange, onDeleteClick) {
        return this.props.source.requestrelation.map((item, index) => this.renderItem(item, index, onChange, onDeleteClick, this.props.source))
    }

    renderOption(item) {
        return {
            name: item.name ? item.name : this.props.labelsrequestrelation.noName, value: item.id
        }
    }

    renderItem(item, index, onChange, onDeleteClick) {
        const formName = `requestrelation[${index}]`

        const onChangeRel = (field, value) => {
            this.props.changeHandler(field, value);
            if (field.endsWith('[request][id]') || field.endsWith('[provider][id]')) {
                this.autofillSlots(index, field, value);
            }
        }

        return (
            <tr key={index} className="request-item">
                <td className="requestrelation-item__request">
                    <Inputs.Hidden name={`${formName}[source]`} value={this.props.source.source} />
                    <Inputs.Select
                        value={item.request.id}
                        name={`${formName}[request][id]`}
                        onChange={onChangeRel}
                        options={this.props.source.requests.map((request) => this.renderOption(request))}
                        attributes={{ "aria-label": this.props.labelsrequestrelation.request }}
                    />
                </td>
                <td className="requestrelation-item__provider">
                    <Inputs.Select
                        value={item.provider.id}
                        name={`${formName}[provider][id]`}
                        onChange={onChangeRel}
                        options={this.props.source.providers.map((provider) => this.renderOption(provider))}
                        attributes={{ "aria-label": this.props.labelsrequestrelation.provider }}
                    />
                </td>
                <td className="requestrelation-item__slots">
                    <Inputs.Text
                        name={`${formName}[slots]`}
                        value={(item.slots) ? item.slots : 1}
                        onChange={onChange}
                        attributes={{ "aria-label": this.props.labelsrequestrelation.slots }}
                    />
                </td>
                <td className="requestrelation-item__public">
                    {(() => {
                        const isPublic =
                            item.public === undefined || item.public === null
                                ? true
                                : (item.public === true || item.public === 1 || item.public === '1');
                        const publicVal = isPublic ? '1' : '0';
                        return (
                            <Inputs.Select
                                name={`${formName}[public]`}
                                value={publicVal}
                                onChange={(_, v) => onChange(`${formName}[public]`, v)}
                                options={[
                                    { name: 'Öffentlich', value: '1' },
                                    { name: 'Nicht öffentlich', value: '0' }
                                ]}
                                attributes={{ "aria-label": this.props.labelsrequestrelation.public }}
                            />
                        );
                    })()}
                </td>
                <td className="requestrelation-item__delete" style={{ verticalAlign: 'middle' }}>
                    <button
                        type="button"
                        className="link button-default requestrelation__delete-button"
                        onClick={() => onDeleteClick(index)}
                        aria-label="Diesen Datensatz löschen"
                        style={{ display: 'inline-flex', alignItems: 'center' }}
                    >
                        <i className="fas fa-trash-alt color-negative" style={{ marginRight: '5px' }} />
                        Löschen
                    </button>
                </td>
            </tr >
        )
    }

    render() {
        const onChange = (field, value) => {
            this.props.changeHandler(field, value)
        }

        const onNewClick = ev => {
            ev.preventDefault()
            getEntity('requestrelation').then((entity) => {
                const { requests = [], providers = [], source } = this.props.source;

                const req = requests.find(r => r.parent_id != null) || requests[0];
                const prv = providers.find(p => p.parent_id != null) || providers[0];

                entity.request  = { id: req ? String(req.id) : '',  source, name: req?.name || '' };
                entity.provider = { id: prv ? String(prv.id) : '', source, name: prv?.name || '' };
                entity.source = source;
                entity.__isNew = true;

                const newIndex = (this.props.source.requestrelation || []).length;
                this.props.addNewHandler('requestrelation', [entity]);
                setTimeout(() => this.autofillSlots(newIndex), 0);
            })
        }

        const onDeleteClick = (index) => {
            const rel = this.props.source.requestrelation[index] || {};
            if (rel?.__isNew === true) {
                return this.props.deleteHandler('requestrelation', index);
            }
            const reqId = rel.request?.id;
            const prvId = rel.provider?.id;

            const req = this.props.source.requests.find(r => String(r.id) === String(reqId));
            const prv = this.props.source.providers.find(p => String(p.id) === String(prvId));

            const rName = req?.name || rel.request?.name || '—';
            const pName = prv?.name || rel.provider?.name || '—';

            const msg = `Kombination „${rName} × ${pName}“ wirklich löschen?\n\nHinweis: Die Änderung wird erst nach „Speichern“ wirksam.`;
            if (window.confirm(msg)) {
                this.props.deleteHandler('requestrelation', index);
            }
        };

        return (
                <div className="requestrelation__list" aria-live="polite" id="liveregionRequestrelationList">
                    <table className="table--base">
                        <thead>
                            <tr>
                                <th>{this.props.labelsrequestrelation.request}</th>
                                <th>{this.props.labelsrequestrelation.provider}</th>
                                <th>{this.props.labelsrequestrelation.slots}</th>
                                <th>{this.props.labelsrequestrelation.public}</th>
                                <th>{this.props.labelsrequestrelation.delete}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {this.getRequestRelation(onChange, onDeleteClick)}
                        </tbody>
                    </table>
                    <div className="table-actions">
                        <button className="link button-default requestrelation--new" onClick={onNewClick}><i className="fas fa-plus-square color-positive"></i> {this.props.labelsrequestrelation.new}</button>
                    </div>
                </div>
        )
    }
}

RequestRelationView.propTypes = {
    labelsrequestrelation: PropTypes.object.isRequired,
    source: PropTypes.object.isRequired,
    changeHandler: PropTypes.func,
    addNewHandler: PropTypes.func,
    deleteHandler: PropTypes.func,
    descriptions: PropTypes.object
}

export default RequestRelationView

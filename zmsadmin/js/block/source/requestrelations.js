import React, { Component } from 'react'
import PropTypes from 'prop-types'
import * as Inputs from '../../lib/inputs'
import { getEntity } from '../../lib/schema'

class RequestRelationView extends Component {
    constructor(props) {
        super(props)
    }

    componentDidUpdate() {
        //console.log("updated requestrelation component")
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

        return (
            <tr key={index} className="request-item">
                <td className="requestrelation-item__request">
                    <Inputs.Hidden
                        name={`${formName}[source]`}
                        value={this.props.source.source}
                    />
                    <Inputs.Select
                        value={item.request.id}
                        name={`${formName}[request][id]`}
                        {...{ onChange }}
                        options={
                            this.props.source.requests.map((request) => this.renderOption(request))
                        } {...{ onChange }}
                        attributes={{ "aria-label": this.props.labelsrequestrelation.request }}
                    />
                </td>
                <td className="requestrelation-item__provider">
                    <Inputs.Select
                        value={item.provider.id}
                        name={`${formName}[provider][id]`}
                        {...{ onChange }}
                        options={
                            this.props.source.providers.map((provider) => this.renderOption(provider))
                        } {...{ onChange }}
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
                entity.source = this.props.source.source
                this.props.addNewHandler('requestrelation', [entity])
            })
        }

        const onDeleteClick = index => {
            this.props.deleteHandler('requestrelation', index)
        }

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

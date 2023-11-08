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
                <td className="request-item__delete">
                    <div className="form-check">
                        <label className="checkboxdeselect requestrelation__delete-button form-check-label">
                            <input className="form-check-input" type="checkbox" readOnly={true} checked="checked" onClick={() => onDeleteClick(index)} role="button" aria-label="Diesen Datensatz löschen" />
                            <span>Löschen</span>
                        </label>
                    </div>
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
            <fieldset>
                <div className="requestrelation__list" aria-live="polite" id="liveregionRequestrelationList">
                    <table className="table--base">
                        <thead>
                            <tr>
                                <th>{this.props.labelsrequestrelation.request}</th>
                                <th>{this.props.labelsrequestrelation.provider}</th>
                                <th>{this.props.labelsrequestrelation.slots}</th>
                                <th>{this.props.labelsrequestrelation.delete}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {this.getRequestRelation(onChange, onDeleteClick)}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colSpan="4">
                                    <Inputs.Description
                                        value={this.props.descriptions.requestrelation}
                                    />
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div className="table-actions">
                        <button className="link button-default requestrelation--new" onClick={onNewClick}><i className="fas fa-plus-square color-positive"></i> {this.props.labelsrequestrelation.new}</button>
                    </div>
                </div>
            </fieldset>
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

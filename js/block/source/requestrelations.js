import React, { Component, PropTypes } from 'react'
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
            <tr className="request-item">
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
                    />
                </td>
                <td className="requestrelation-item__slots">
                    <Inputs.Text
                        name={`${formName}[slots]`}
                        value={(item.slots) ? item.slots : 1}
                        onChange={onChange}
                    />
                </td>
                <td className="request-item__delete">
                    <label className="checkboxdeselect requestrelation__delete-button">
                        <input type="checkbox" checked={true} onClick={() => onDeleteClick(index)} /><span></span>
                    </label>
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
                console.log(entity)
                this.props.addNewHandler('requestrelation', [entity])
            })
        }

        const onDeleteClick = index => {
            this.props.deleteHandler('requestrelation', index)
        }

        return (
            <fieldset>
                <div className="requestrelation__list">
                    <Inputs.Description
                        children={this.props.descriptions.requestrelation}
                    />
                    <table className="clean">
                        <thead>
                            <th>{this.props.labelsrequestrelation.request}</th>
                            <th>{this.props.labelsrequestrelation.provider}</th>
                            <th>{this.props.labelsrequestrelation.slots}</th>
                            <th>{this.props.labelsrequestrelation.delete}</th>
                        </thead>
                        <tbody>
                            {this.getRequestRelation(onChange, onDeleteClick)}
                            <tr>
                                <td colSpan="4">
                                    <button className="button-default requestrelation--new" onClick={onNewClick}>{this.props.labelsrequestrelation.new}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        )
    }
}

RequestRelationView.propTypes = {
    labelsrequestrelation: PropTypes.array.isRequired,
    source: PropTypes.array.isRequired,
    changeHandler: PropTypes.changeHandler,
    addNewHandler: PropTypes.addNewHandler,
    deleteHandler: PropTypes.deleteHandler,
    descriptions: PropTypes.array
}

export default RequestRelationView

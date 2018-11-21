import React, { Component, PropTypes } from 'react'
import $ from "jquery"
import * as Inputs from '../../lib/inputs'
import { getEntity } from '../../lib/schema'

const renderRequest = (request, index, onChange, onDeleteClick, labels, descriptions, source) => {
    const formName = `requests[${index}]`

    return (
        <tr className="request-item">
            <td className="request-item__id" width="12%">
                <Inputs.Text
                    name={`${formName}[id]`}
                    placeholder={labels.id}
                    value={request.id}
                    attributes={{ "readOnly": "1" }}
                />
            </td>
            <td className="request-item__name" width="28%">
                <Inputs.Text
                    name={`${formName}[name]`}
                    placeholder={labels.name}
                    value={request.name}
                    onChange={onChange}
                />
            </td>
            <td className="request-item__link">
                <Inputs.FormGroup>
                    <Inputs.Label
                        children={`${labels.url}`}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[link]`}
                            placeholder={labels.url}
                            value={request.link}
                            onChange={onChange}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup>
                    <Inputs.Label
                        children={labels.group}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[group]`}
                            placeholder={labels.group}
                            value={request.group}
                            onChange={onChange}
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
                            value={(request.data) ? JSON.stringify(request.data) : ''}
                            placeholder="{}"
                            onChange={onChange}
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
            <td className="request-item__delete">
                <label className="checkboxdeselect request__delete-button">
                    <input type="checkbox" checked={true} onClick={() => onDeleteClick(index)} /><span></span>
                </label>
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

    getRequestsWithLabels(onChange, onDeleteClick) {
        return this.props.source.requests.map((request, index) => renderRequest(request, index, onChange, onDeleteClick, this.props.labelsrequests, this.props.descriptions, this.props.source.source))
    }

    hideDeleteButton() {
        $('.request-item').each((index, item) => {
            if ($(item).find('.request-item__id input').val()) {
                $(item).find('.request__delete-button').css("visibility", "hidden");
            }
        })
    }

    componentDidMount() {
        console.log("mounted request component")
        this.hideDeleteButton()
    }

    componentDidUpdate() {
        //console.log("updated request component")
    }

    componentWillReceiveProps(nextProps) {
        if (nextProps.source.source !== this.props.source) {
            //console.log("props changed", nextProps)
        }
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
            <div className="requests__list">
                <table className="clean">
                    <thead>
                        <th>LfdNr.</th>
                        <th>Bezeichnung</th>
                        <th>Link und weitere Daten</th>
                    </thead>
                    <tbody>
                        {this.getRequestsWithLabels(onChange, onDeleteClick)}
                        <tr>
                            <td colSpan="4">
                                <button className="button-default" onClick={onNewClick}>Neue Dienstleistung</button>
                                <Inputs.Description
                                    children={this.props.descriptions.delete}
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        )
    }
}

RequestsView.propTypes = {
    labelsrequests: PropTypes.array.isRequired,
    descriptions: PropTypes.array.isRequired,
    source: PropTypes.array.isRequired,
    changeHandler: PropTypes.changeHandler,
    addNewHandler: PropTypes.addNewHandler,
    deleteHandler: PropTypes.deleteHandler
}

export default RequestsView

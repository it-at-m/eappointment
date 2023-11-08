import React, { Component } from 'react'
import PropTypes from 'prop-types'
import $ from "jquery"
import * as Inputs from '../../lib/inputs'
import { getEntity } from '../../lib/schema'

const renderRequest = (request, index, onChange, onDeleteClick, labels, descriptions, source) => {
    const formName = `requests[${index}]`

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
            <td className="request-item__delete">
                <div className="form-check">
                    <label className="checkboxdeselect request__delete-button form-check-label">
                        <input className="form-check-input" type="checkbox" readOnly={true} checked={true} onClick={() => onDeleteClick(index)} role="button" />
                        <span>Löschen</span>
                    </label>
                </div>
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
                            <th>Link und weitere Daten</th>
                            <th>Löschen</th>
                        </tr>
                    </thead>
                    <tbody>
                        {this.getRequestsWithLabels(onChange, onDeleteClick)}
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
    deleteHandler: PropTypes.func
}

export default RequestsView

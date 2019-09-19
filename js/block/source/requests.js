import React, { Component, PropTypes } from 'react'
import $ from "jquery"
import * as Inputs from '../../lib/inputs'
import { getEntity } from '../../lib/schema'

const renderRequest = (request, index, onChange, onDeleteClick, labels, descriptions, source) => {
    const formName = `requests[${index}]`

    return (
        <tr className="request-item">
            <td className="request-item__id" width="auto">
                <Inputs.Text
                    name={`${formName}[id]`}
                    placeholder={labels.id}
                    value={request.id}
                    attributes={{ "readOnly": "1", "aria-label": "Laufende Nummer"}}
                />
            </td>
            <td className="request-item__name" width="auto">
                <Inputs.Text
                    name={`${formName}[name]`}
                    placeholder={labels.name}
                    value={request.name}
                    onChange={onChange}
                    attributes={{"aria-label": "Bezeichnung"}}
                />
            </td>
            <td className="request-item__link">
                <Inputs.FormGroup className="form-group--inline">
                    <Inputs.Label
                        children={`${labels.url}`}
                        attributes={{"htmlFor": `requestUrl${index}` }}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[link]`}
                            placeholder={labels.url}
                            value={request.link}
                            onChange={onChange}
                            attributes={{"id": `requestUrl${index}`}}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup className="form-group--inline">
                    <Inputs.Label
                        children={labels.group}
                        attributes={{"htmlFor": `requestGroup${index}`}}
                    />
                    <Inputs.Controls>
                        <Inputs.Text
                            name={`${formName}[group]`}
                            placeholder={labels.group}
                            value={request.group}
                            onChange={onChange}
                            attributes={{"id": `requestGroup${index}`}}
                        />
                    </Inputs.Controls>
                </Inputs.FormGroup>
                <Inputs.FormGroup className="form-group--inline">
                    <Inputs.Label
                        children={labels.data}
                        attributes={{"htmlFor": `requestData${index}`}}
                    />
                    <Inputs.Controls>
                        <Inputs.Textarea
                            name={`${formName}[data]`}
                            value={(request.data) ? JSON.stringify(request.data) : ''}
                            placeholder="{}"
                            onChange={onChange}
                            attributes={{"id": `requestData${index}`, "aria-describedby": `help_requestData${index}`}}
                        />
                        <Inputs.Description
                            children={descriptions.data}
                            attributes={{"id": `help_requestData${index}`}}
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
                    <input type="checkbox" checked={true} onClick={() => onDeleteClick(index)} role="button" aria-label="Diesen Datensatz löschen" /><span title="Löschen"></span>
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
            <div className="table-responsive-wrapper requests__list" aria-live="polite" id="liveregionRequestList">
                <table className="table--base">
                    <thead>
                        <tr>
                            <th>LfdNr.</th>
                            <th>Bezeichnung</th>
                            <th>Link und weitere Daten</th>
                            <th></th>
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
                                    children={this.props.descriptions.delete}
                                />
                                </p>
                                <button className="link button-default" onClick={onNewClick}><i className="fas fa-plus-square color-positive" aria-hidden="true"></i> Neue Dienstleistung</button>
                            </td>
                        </tr>
                    </tfoot>
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

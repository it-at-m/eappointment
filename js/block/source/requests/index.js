import React, { Component, PropTypes } from 'react'
import $ from "jquery"
import * as Inputs from '../../../lib/inputs'

const renderRequest = (request, index, onChange, onDeleteClick, labels, descriptions, source) => {
    const formName = `requests[${index}]`

    const onChangeName = (_, value) => onChange(index, 'name', value)
    const onChangeLink = (_, value) => onChange(index, 'link', value)
    const onChangeGroup = (_, value) => onChange(index, 'group', value)
    const onChangeData = (_, value) => onChange(index, 'data', value)

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
                    onChange={onChangeName}
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
                            onChange={onChangeLink}
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
                            onChange={onChangeGroup}
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
        this.state = {
            requests: props.requests.length > 0
                ? props.requests
                : [{
                    source: props.source.source ? props.source.source : '',
                    id: 1,
                    name: '',
                    link: '',
                    group: '',
                    data: ''
                }],
            labels: props.labelsrequests,
            descriptions: props.descriptions,
            source: props.source
        }
    }

    changeItemField(index, field, value) {
        //console.log('change item field', index, field, value)
        this.setState({
            requests: this.state.requests.map((request, requestIndex) => {
                return index === requestIndex ? Object.assign({}, request, { [field]: value }) : request
            })
        })
    }

    getNextId() {
        let nextId = Number(this.state.requests[this.state.requests.length - 1].id) + 1
        return nextId;
        //return this.state.requests.pop().id;
    }

    addNewItem() {
        this.setState({
            requests: this.state.requests.concat([{
                source: this.state.source,
                id: this.getNextId(),
                name: '',
                link: '',
                group: '',
                data: ''
            }])
        })
    }

    deleteItem(deleteIndex) {
        this.setState({
            requests: this.state.requests.filter((request, index) => {
                return index !== deleteIndex
            })
        })
    }

    getRequestsWithLabels(onChange, onDeleteClick) {
        return this.state.requests.map((request, index) => renderRequest(request, index, onChange, onDeleteClick, this.state.labels, this.state.descriptions, this.state.source.source))
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
        console.log("updated request component")
    }

    componentWillReceiveProps(nextProps) {
        // You don't have to do this check first, but it can help prevent an unneeded render
        if (nextProps.source.source !== this.state.source) {
            this.setState({ source: nextProps.source })
        }
    }

    render() {
        //console.log('requestsView::render', this.state)

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
            <div className="department-requests__list">
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

RequestsView.propTypes = {
    requests: PropTypes.array,
    labelsrequests: PropTypes.array.isRequired,
    descriptions: PropTypes.array.isRequired,
    source: PropTypes.array.isRequired
}

export default RequestsView

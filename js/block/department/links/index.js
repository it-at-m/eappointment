import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { getEntity } from '../../../lib/schema'
import * as Inputs from '../../../lib/inputs'

const renderLink = (organisationId, link, index, onChange, onDeleteClick) => {
    const formName = `links[${index}]`

    const onChangeName = (_, value) => onChange(index, 'name', value)
    const onChangeUrl = (_, value) => onChange(index, 'url', value)
    const onChangeTarget = (_, value) => onChange(index, 'target', value)
    const onChangePublic = (_, value) => onChange(index, 'public', value)
    const onChangeOrganisation = (_, value) => onChange(index, 'organisation', value)
    const onDelete = ev => {
        ev.preventDefault()
        onDeleteClick(index)
    };

    return (
        <tr className="link-item" key={index}>
            <td className="link-item__name">
                <Inputs.Text
                    name={`${formName}[name]`}
                    value={link.name}
                    onChange={onChangeName}
                    placeholder="Name"
                    attributes={{"aria-label":"Bezeichnung"}}
                />
            </td>
            <td className="link-item__url">
                <Inputs.Text
                    name={`${formName}[url]`}
                    value={link.url}
                    onChange={onChangeUrl}
                    placeholder="URL"
                    attributes={{"aria-label":"URL"}}
                />
            </td>
            <td className="link-item__settings">
                <div className="form-check">
                <Inputs.Checkbox
                    name={`${formName}[target]`}
                    key="In neuem Fenster öffnen"
                    label="in neuem Fenster"
                    onChange={onChangeTarget}
                    value={link.target}
                    checked={1 == link.target}
                />
                </div>
            </td>
            <td className="link-item__settings">
                <div className="form-check">
                <Inputs.Checkbox
                    name={`${formName}[public]`}
                    key="Externer Link"
                    title="Link hat eine öffentliche URL"
                    label="öffentliche URL (kursiv)"
                    onChange={onChangePublic}
                    value={link.public}
                    checked={1 == link.public}
                />
                </div>
            </td>
            <td className="link-item__settings">
                <div className="form-check">
                    <Inputs.Checkbox
                        name={`${formName}[organisation]`}
                        key="Für gesamte Organisation"
                        title="Für gesamte Organisation aktivieren"
                        label="Organisation"
                        onChange={onChangeOrganisation}
                        value={organisationId}
                        checked={0 < link.organisation}
                    />
                </div>
            </td>
            <td className="link-item__settings">
                <div className="form-check">
                    <a href="#" className="icon" title="Link entfernen" aria-label="Link entfernen" onClick={onDelete}>
                        <i className="far fa-trash-alt" aria-hidden="true"></i>
                    </a>
                </div>
            </td>
        </tr>
    )
}

class LinksView extends Component {
    constructor(props) {
        super(props)
        this.state = { 
            links: [],
            organisation: this.props.organisation
        }
    }

    componentDidMount() {
        getEntity('link').then((entity) => {
            this.setState({
                links: this.props.links.length > 0 ? this.props.links : [entity]
            })
        })
    }

    changeItemField(index, field, value) {
        //console.log('change item field', index, field, value)
        this.setState({
            links: this.state.links.map((link, linkIndex) => {
                return index === linkIndex ? Object.assign({}, link, { [field]: value }) : link
            })
        })
    }

    addNewItem() {
        getEntity('link').then((entity) => {
            this.setState({
                links: this.state.links.concat([entity])
            })
        })

    }

    deleteItem(deleteIndex) {
        this.setState({
            links: this.state.links.filter((link, index) => {
                return index !== deleteIndex
            })
        })
    }

    render() {
        console.log('LinksView::render', this.state)

        const onNewClick = ev => {
            ev.preventDefault()
            this.addNewItem()
        }

        const onDeleteClick = (index) => {
            this.deleteItem(index)
        }

        const onChange = (index, field, value) => {
            this.changeItemField(index, field, value)
        }

        return (
            <div className="department-links__list table-responsive-wrapper">
                <div className="table-action-link">
                    <button className="link button-default" onClick={onNewClick} >
                        <i className="fas fa-plus-square color-positive"></i> Neuer Link
                    </button>
                </div>
                <table className="table--base clean">
                    <thead>
                        <tr>
                            <th>Bezeichnung</th>
                            <th>Link</th>
                            <th colSpan="4">Linkeinstellungen</th>
                        </tr>
                    </thead>
                    <tbody>
                        {this.state.links.map((link, index) => renderLink(this.state.organisation, link, index, onChange, onDeleteClick))}
                    </tbody>
                </table>
                <div className="table-action-link">
                    <button className="link button-default" onClick={onNewClick} >
                        <i className="fas fa-plus-square color-positive"></i> Neuer Link
                    </button>
                </div>
            </div>
        )
    }
}

LinksView.propTypes = {
    links: PropTypes.array,
    organisation: PropTypes.number
}

export default LinksView
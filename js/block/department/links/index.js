import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { getEntity } from '../../../lib/schema'
import * as Inputs from '../../../lib/inputs'

const renderLink = (link, index, onChange, onDeleteClick) => {
    const formName = `links[${index}]`

    const onChangeName = (_, value) => onChange(index, 'name', value)
    const onChangeUrl = (_, value) => onChange(index, 'url', value)
    const onChangeTarget = (_, value) => onChange(index, 'target', value)

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
            <td className="link-item__target">
                <div className="form-check">
                    <label className="checkbox-label">
                        <Inputs.Checkbox
                            name={`${formName}[target]`}
                            key="In neuem Fenster öffnen"
                            onChange={onChangeTarget}
                            value={link.target}
                            checked={1 == link.target}
                        />
                        <span className="form-check-label">Im neuen Fenster öffnen</span>
                    </label>
                </div>
            </td>
            <td className="link-item__delete">
                <div className="form-check">
                    <label className="checkboxdeselect link__delete-button">
                        <input type="checkbox" checked={true} onChange={() => {}} onClick={() => onDeleteClick(index)} />
                        <span className="form-check-label">Löschen</span>
                    </label>
                </div>
            </td>
        </tr>
    )
}

class LinksView extends Component {
    constructor(props) {
        super(props)
        this.state = { links: [] }
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

        const onDeleteClick = index => {
            this.deleteItem(index)
        }

        const onChange = (index, field, value) => {
            this.changeItemField(index, field, value)
        }

        return (
            <div className="department-links__list table-responsive-wrapper">
                <table className="table--base clean">
                    <thead>
                        <tr>
                            <th>Bezeichnung</th>
                            <th>Link</th>
                            <th>Im neuen Fenster öffnen</th>
                            <th>Löschen</th>
                        </tr>
                    </thead>
                    <tbody>
                        {this.state.links.map((link, index) => renderLink(link, index, onChange, onDeleteClick))}
                    </tbody>
                </table>
                <div className="table-actions">
                    <button className="link button-default" onClick={onNewClick} >
                        <i className="fas fa-plus-square color-positive" aria-hidden="true"></i> Neuer Link
                    </button>
                </div>
            </div>
        )
    }
}

LinksView.propTypes = {
    links: PropTypes.array
}

export default LinksView
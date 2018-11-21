import React, { Component, PropTypes } from 'react'
import { getEntity } from '../../../lib/schema'
import * as Inputs from '../../../lib/inputs'

const renderLink = (link, index, onChange, onDeleteClick) => {
    const formName = `links[${index}]`

    const onChangeName = (_, value) => onChange(index, 'name', value)
    const onChangeUrl = (_, value) => onChange(index, 'url', value)
    const onChangeTarget = (_, value) => onChange(index, 'target', value)

    return (
        <tr className="link-item">
            <td className="link-item__name">
                <Inputs.Text
                    name={`${formName}[name]`}
                    placeholder="Name"
                    value={link.name}
                    onChange={onChangeName}
                />
            </td>
            <td className="link-item__url">
                <Inputs.Text
                    name={`${formName}[url]`}
                    placeholder="URL"
                    value={link.url}
                    onChange={onChangeUrl}
                />
            </td>
            <td className="link-item__target">
                <label className="checkbox-label">
                    <Inputs.Checkbox
                        name={`${formName}[target]`}
                        key="In neuem Fenster öffnen"
                        onChange={onChangeTarget}
                        value={link.target}
                        checked={1 == link.target}
                    />

                </label>
            </td>
            <td className="link-item__delete">
                <label className="checkboxdeselect link__delete-button">
                    <input type="checkbox" checked={true} onClick={() => onDeleteClick(index)} /><span></span>
                </label>
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
            <div className="department-links__list">
                <table className="clean">
                    <thead>
                        <th>Bezeichnung</th>
                        <th>Link</th>
                        <th>Im neuen Fenster öffnen</th>
                        <th>Löschen</th>
                    </thead>
                    <tbody>
                        {this.state.links.map((link, index) => renderLink(link, index, onChange, onDeleteClick))}
                        <tr><td colSpan="4">
                            <button className="button-default" onClick={onNewClick} >Neuer Link</button>
                        </td></tr>
                        <tr><td colSpan="4">
                            &nbsp;
                    </td></tr>
                    </tbody>
                </table>
            </div>
        )
    }
}

LinksView.propTypes = {
    links: PropTypes.array
}

export default LinksView

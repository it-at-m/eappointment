import React, { Component, PropTypes } from 'react'

import * as Inputs from '../../../lib/inputs'

const renderLink = (link, index, onChange, onDeleteClick) => {
    const formName = `links[${index}]`

    const onChangeName = (_, value) => onChange(index, 'name', value)
    const onChangeUrl = (_, value) => onChange(index, 'url', value)
    const onChangeTarget = (_, value) => onChange(index, 'target', value)

    return (
        <div className="link-item">
            <div className="link-item__name">
                <Inputs.Text
                    name={`${formName}[name]`}
                    placeholder="Name"
                    value={link.name}
                    onChange={onChangeName}
                />
            </div>
            <div className="link-item__url">
                <Inputs.Text
                    name={`${formName}[url]`}
                    placeholder="URL"
                    value={link.url}
                    onChange={onChangeUrl}
                />
            </div>
            <div className="link-item__target">
                <label class="checkbox-label">
                    <Inputs.Checkbox
                        name={`${formName}[target]`}
                        key="In neuem Fenster öffnen"
                        onChange={onChangeTarget}
                        value={link.target}
                        checked={1 == link.target}
                    />
                    In neuem Fenster öffnen
                </label>
            </div>
            <div className="link-item__delete">
                <label className="checkboxdeselect link__delete-button">
                    <input type="checkbox" checked={true} onClick={() => onDeleteClick(index)} /><span>Löschen</span>
                </label>
            </div>
        </div>
    )
}

class LinksView extends Component {
    constructor(props) {
        super(props)
        this.state = {
            links: props.links.length > 0 ? props.links : [{ name: '', url: '', target: 0}]
        }
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
        this.setState({
            links: this.state.links.concat([{ name: '', url: '', target: 0 }])
        })
    }

    deleteItem(deleteIndex) {
        this.setState({
            links: this.state.links.filter( (link, index) => {
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
            <div className="form-group links">
                <label className="label">Links:</label>
                <div className="controls">
                    <div className="department-links__list">
                    {this.state.links.map((link, index) => renderLink(link, index, onChange, onDeleteClick))}
                    </div>
                    <button className="button-default" onClick={onNewClick} >Neuer Link</button>
                </div>
            </div>
        )
    }
}

LinksView.propTypes = {
    links: PropTypes.array
}

export default LinksView

import React, { Component, PropTypes } from 'react'

import * as Inputs from '../../../lib/inputs'
const { Description, FormGroup, Label, Controls } = Inputs

const readPropsCluster = cluster => {
    const { name, id } = cluster

    return {
        type: 'c',
        id,
        name
    }
}

const readPropsScope = scope => {
    const { shortName, contact, id } = scope

    return {
        type: 's',
        id,
        shortName,
        contact
    }
}

class TicketPrinterConfigView extends Component {
    constructor(props) {
        super(props)

        console.log('TicketPrinterConfigView::constructor', props)

        this.state = {
            selectedItems: [],
            departments: props.departments.map(department => {
                const { name, id, scopes = [], clusters = [] } = department
                return {
                    id,
                    name,
                    clusters: clusters.map(readPropsCluster),
                    scopes: scopes.map(readPropsScope)
                }
            }),
            generatedUrl: "",
            homeUrl: "",
            ticketPrinterName: ""
        }
    }

    buildUrl() {
        const itemList = this.state.selectedItems.map(item => `${item.type}${item.id}`).join(',')
        const baseUrl = this.props.config.ticketprinter.baseUrl

        let parameters = []

        if (itemList) {
            parameters.push(`ticketprinter[buttonlist]=${itemList}`)
        }

        if (this.state.ticketPrinterName) {
            parameters.push(`ticketprinter[name]=${this.state.ticketPrinterName}`)
        }

        if (this.state.homeUrl) {
            parameters.push(`ticketprinter[home]=${this.state.homeUrl}`)
        }

        return `${baseUrl}?${parameters.join('&')}`
    }

    renderNumberSelect(value, onNumberChange) {
        const onChange = ev => onNumberChange(ev.target.value)

        const usedSlots = this.state.selectedItems.map(item => item.position)
        const availableSlots = [null, 1, 2, 3, 4, 5, 6].filter(slot => usedSlots.indexOf(slot) < 0 || slot === value)

        return (
            <select {... { onChange, value }} >
                {availableSlots.map(n => <option value={n}>{n || 'nicht anzeigen'}</option>)}
            </select>
        )
    }

    showItem(item, position) {
        const items = this.state.selectedItems.filter(i => i.id !== item.id)
        const newItem = Object.assign({}, item, { position })
        items.push(newItem)
        items.sort((a,b) => {
            const aPos = a.position
            const bPos = b.position

            if (aPos < bPos) { return -1 }
            if (aPos > bPos) { return 1 }
            return 0
        })

        this.setState({ selectedItems: items })
    }

    hideItem(item) {
        const items = this.state.selectedItems.filter(i => i.id !== item.id)
        this.setState({ selectedItems: items })
    }

    renderItem(item) {
        const onChange = n => {
            const position = parseInt(n, 10)

            if (position) {
                this.showItem(item, position)
            } else {
                this.hideItem(item)
            }
        }

        const text = `${item.contact ? item.contact.name : item.name} ${item.shortName ? item.shortName : ""}`
        const prefix = item.type === 'c' ? 'Cluster: ' : ''
        const position = (this.state.selectedItems.filter(i => i.id === item.id)[0] || {}).position

        return (
            <div className="ticketprinter-config__item">
                <label>{prefix}{text}</label>
                <span>
                    {this.renderNumberSelect(position, onChange)}
                </span>
            </div>
        )
    }

    renderScopes(scopes) {
        if (scopes.length > 0) {
            return (
                <div className="form-group">
                    <Label>Standort</Label>
                    <Controls>
                        {scopes.map(this.renderItem.bind(this))}
                    </Controls>
                </div>
            )
        }
    }

    renderClusters(clusters) {
        if (clusters.length > 0) {
            return (
                <div className="form-group">
                    <Label>Standort­gruppe</Label>
                    <Controls>
                        {clusters.map(this.renderItem.bind(this))}
                    </Controls>
                </div>
            )
        }
    }

    renderDepartment(department) {
        return (
            <div>
                <h2>{department.name}</h2>
                {this.renderClusters(department.clusters)}
                {this.renderScopes(department.scopes)}
            </div>
        )
    }

    render() {
        const onNameChange = (name, value) => {
            this.setState({ticketPrinterName: value})
        }

        const onHomeChange = (name, value) => {
            this.setState({homeUrl: value})
        }

        const generatedUrl = this.buildUrl()

        return (
            <form className="form-group ticketprinter-config">
                {this.state.departments.map(this.renderDepartment.bind(this))}
                <fieldset>
                    <FormGroup>
                        <Label>Name zur internen Identifikation (optional)</Label>
                        <Controls>
                            <Inputs.Text onChange={onNameChange}/>
                        </Controls>
                    </FormGroup>
                    <FormGroup>
                        <Label>StartUrl (optional)</Label>
                        <Controls>
                            <Inputs.Text onChange={onHomeChange}/>
                            <Description>Tragen Sie eine alternative URL ein, wenn nach der Ausgabe einer Wartenummer eine alternative Startseite aufgerufen werden soll</Description>
                        </Controls>
                    </FormGroup>
                    <FormGroup>
                        <Label>URL</Label>
                        <Controls>
                            <Inputs.Text value={generatedUrl} attributes={{readOnly: true}}/>
                            <a href={generatedUrl} target="_blank" className="btn button-submit">Aktuelle Kiosk-Konfiguration in einem neuen Fenster öffnen</a>
                        </Controls>
                    </FormGroup>
                </fieldset>
            </form>
        )
    }
}

TicketPrinterConfigView.propTypes = {
    departments: PropTypes.array,
    organisation: PropTypes.object,
    config: PropTypes.shape({
        ticketprinter: PropTypes.object
    })
}

export default TicketPrinterConfigView

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import * as Inputs from '../../../lib/inputs'
const { Description, FormGroup, Label, Controls, Select } = Inputs

const readPropsCluster = cluster => {
    const { name, id } = cluster

    return {
        type: 'c',
        id,
        name
    }
}

const readPropsScope = scope => {
    const { shortName, contact, id, services } = scope

    return {
        type: 's',
        id,
        shortName,
        contact,
        services: services.map(readPropsService)
    }
}

const readPropsService = service => {
    const { id, name } = service

    return {
        type: 'r',
        id,
        name
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
            template: "default",
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

        if (this.state.template !== 'default') {
            parameters.push(`template=${this.state.template}`)
        }

        return `${baseUrl}?${parameters.join('&')}`
    }

    renderNumberSelect(value, onNumberChange) {
        const onChange = ev => onNumberChange(ev.target.value)

        const usedSlots = this.state.selectedItems.map(item => item.position)
        const availableSlots = [null, 1, 2, 3, 4, 5, 6].filter(slot => usedSlots.indexOf(slot) < 0 || slot === value)

        return (
            <select {... { onChange, value }} className="form-control">
                {availableSlots.map(n => <option key={n} value={n}>{n ? `Position ${n}` : 'nicht anzeigen'}</option>)}
            </select>
        )
    }

    showItem(item, position) {
        const items = this.state.selectedItems.filter(i => i.id !== item.id)
        const newItem = Object.assign({}, item, { position })
        items.push(newItem)
        items.sort((a, b) => {
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
            <div key={item.type + "-" + item.id} className="form-group--inline ticketprinter-config__item" >
                <label className="light">
                    <span>{prefix}{text}</span>
                    <Controls>
                        {this.renderNumberSelect(position, onChange)}
                    </Controls>
                </label>
            </div >
        )
    }

    renderScopes(scopes) {
        if (scopes.length > 0) {
            return (
                <div>
                    <fieldset key="scopeList">
                        <legend className="label">Standorte</legend>
                        {scopes.map(this.renderScope.bind(this))}
                    </fieldset>
                </div>
            )
        }
    }

    renderServices(services) {
        if (services.length > 0) {
            return (
                <fieldset key="scopeList">
                    <legend className="label">Dienstleistungen</legend>
                    {services.map(this.renderItem.bind(this))}
                </fieldset>
            )
        }
    }

    renderClusters(clusters) {
        if (clusters.length > 0) {
            return (
                <fieldset key="clusterList">
                    <legend className="label">Standort­gruppe</legend>
                    {clusters.map(this.renderItem.bind(this))}
                </fieldset>
            )
        }
    }

    renderScope(scope) {
        return (
            <div key={scope.id}>
                <h2 className="block__heading">{scope.shortName}</h2>
                {[scope].map(this.renderItem.bind(this))}
                {this.renderServices(scope.services)}
            </div>
        )
    }

    renderDepartment(department) {
        return (
            <div key={department.id}>
                <h2 className="block__heading">{department.name}</h2>
                {this.renderScopes(department.scopes)}
                {/* this.renderClusters(department.clusters) */}
            </div>
        )
    }

    render() {
        const onNameChange = (name, value) => {
            this.setState({ ticketPrinterName: value })
        }

        const onHomeChange = (name, value) => {
            this.setState({ homeUrl: value })
        }

        const onTemplateStatusChange = (_, value) => {
            this.setState({
                template: value
            })
        }

        const generatedUrl = this.buildUrl()

        return (
            <form className="form--base ticketprinter-config">
                {this.state.departments.map(this.renderDepartment.bind(this))}
                <fieldset key="ticketprinter-fieldset">
                    <FormGroup key="ticketprinter-name">
                        <Label attributes={{ "htmlFor": "ticketprinterName" }} value="Name zur internen Identifikation (optional)"></Label>
                        <Controls>
                            <Inputs.Text
                                attributes={{ "id": "ticketprinterName" }}
                                onChange={onNameChange}
                            />
                        </Controls>
                    </FormGroup>
                    <FormGroup key="ticketprinter-Starturl">
                        <Label attributes={{ "htmlFor": "ticketprinterStarturl" }} value="StartUrl (optional)"></Label>
                        <Controls>
                            <Inputs.Text
                                attributes={{ "id": "ticketprinterStarturl", "aria-describedby": "help_ticketprinterStarturl" }}
                                onChange={onHomeChange}
                            />
                            <Description attributes={{ "id": "help_ticketprinterStarturl" }}>Tragen Sie eine alternative URL ein, wenn nach der Ausgabe einer Wartenummer eine alternative Startseite aufgerufen werden soll</Description>
                        </Controls>
                    </FormGroup>
                    <FormGroup key="ticketprinter-layout">
                        <Label attributes={{ "htmlFor": "ticketprinterLayout" }} value="Layout"></Label>
                        <Controls>
                            <Select
                                attributes={{ "id": "ticketprinterLayout" }}
                                options={[
                                    { name: 'Standard', value: 'default' },
                                    { name: 'Mit wartenden Kunden', value: 'wait' },
                                    { name: 'Mit voraussichtlicher Wartezeit', value: 'time' },
                                    { name: 'Mit wartenden Kunden und voraussichtlicher Wartezeit', value: 'timewait' }
                                ]}
                                value={this.state.template}
                                onChange={onTemplateStatusChange} />
                        </Controls>
                    </FormGroup>
                    <FormGroup key="ticketprinter-url">
                        <Label attributes={{ "htmlFor": "ticketprinterUrl" }} value="URL"></Label>
                        <Controls>
                            <Inputs.Text
                                value={generatedUrl} attributes={{ readOnly: true, "id": "ticketprinterUrl" }} />
                        </Controls>
                    </FormGroup>
                    <div className="form-actions">
                        <a href={generatedUrl} target="_blank" rel="noopener noreferrer" className="button button-submit"><i className="fas fa-external-link-alt"></i> Aktuelle Kiosk-Konfiguration in einem neuen Fenster öffnen</a>
                    </div>
                </fieldset>
            </form >
        )
    }
}

TicketPrinterConfigView.propTypes = {
    departments: PropTypes.array,
    organisation: PropTypes.object,
    config: PropTypes.shape({
        ticketprinter: PropTypes.shape({
            baseUrl: PropTypes.object
        })
    })
}

export default TicketPrinterConfigView

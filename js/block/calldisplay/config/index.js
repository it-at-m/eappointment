import React, { Component, PropTypes } from 'react'

import * as Inputs from '../../../lib/inputs'
const { FormGroup, Label, Controls } = Inputs

const readPropsCluster = cluster => {
    const { name, id } = cluster

    return {
        type: 'cluster',
        id,
        name
    }
}

const readPropsScope = scope => {
    const { name, id } = scope

    return {
        type: 'scope',
        id,
        name
    }
}

class CallDisplayConfigView extends Component {
    constructor(props) {
        super(props)

        console.log('CallDisplayConfigView::constructor', props)

        const { departments } = props.organisation

        this.state = {
            selectedItems: [],
            departments: departments.map(department => {
                const { name, id, scopes = [], clusters = [] } = department
                return {
                    id,
                    name,
                    clusters: clusters.map(readPropsCluster),
                    scopes: scopes.map(readPropsScope)
                }
            }),
            generatedUrl: ""
        }
    }

    buildUrl() {
        const baseUrl = this.props.config.calldisplay.baseUrl

        const collections = this.state.selectedItems.reduce((carry, current) => {
            if (current.type === "cluster") {
                carry.scopelist.push(current.id)
            } else if (current.type === "scope") {
                carry.clusterlist.push(current.id)
            }

            return carry
        }, {
            scopelist: [],
            clusterlist: []
        })

        let parameters = []

        if (collections.scopelist.length > 0) {
            parameters.push(`calldisplay[scopelist]=${collections.scopelist.join(",")}`)
        }

        if (collections.clusterlist.length > 0) {
            parameters.push(`calldisplay[clusterlist]=${collections.clusterlist.join(",")}`)
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

        const position = (this.state.selectedItems.filter(i => i.id === item.id)[0] || {}).position

        return (
            <div className="ticketprinter-config__item">
                <label>{`${item.name} (${item.id})`}</label>
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
        const generatedUrl = this.buildUrl()

        return (
            <form className="form-group calldisplay-config">
                {this.state.departments.map(this.renderDepartment.bind(this))}
                <fieldset>
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

CallDisplayConfigView.propTypes = {
    organisation: PropTypes.shape({
        departments: PropTypes.shape({
            clusters: PropTypes.array,
            scopes: PropTypes.array
        })
    }),
    config: PropTypes.shape({
        calldisplay: PropTypes.object
    })
}

export default CallDisplayConfigView

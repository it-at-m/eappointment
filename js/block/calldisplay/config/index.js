import React, { Component } from 'react'
import PropTypes from 'prop-types'

import * as Inputs from '../../../lib/inputs'
import QrCodeView from "../qrCode";

const { FormGroup, Label, Controls, Select } = Inputs

const readPropsCluster = cluster => {
    const { name, id } = cluster

    return {
        type: 'cluster',
        id,
        name
    }
}

const readPropsScope = scope => {
    const { shortName, contact, id } = scope
    return {
        type: 'scope',
        id,
        shortName,
        contact
    }
}

class CallDisplayConfigView extends Component {
    constructor(props) {
        super(props)
        this.includeurl = props.includeurl || "";
        //console.log('CallDisplayConfigView::constructor', props)

        this.state = {
            selectedItems: [],
            departments: props.departments.map(department => {
                const { name, id, scopes = [], clusters = [] } = department
                return {
                    name,
                    id,
                    scopes: scopes.map(readPropsScope),
                    clusters: clusters.map(readPropsCluster)
                }
            }),
            queueStatus: 'all',
            template: 'defaultplatz',
            hmac: '',
            enableQrCode: false,
            showQrCode: false
        }

        this.signParameters(this.state)
    }

    getSelectedItemsCollection(state) {
        return state.selectedItems.reduce((carry, current) => {
            if (current.type === "cluster") {
                carry.clusterlist.push(current.id)
            } else if (current.type === "scope") {
                carry.scopelist.push(current.id)
            }

            return carry
        }, {
            scopelist: [],
            clusterlist: []
        })
    }

    buildCalldisplayUrl() {
        const baseUrl  = this.props.config.calldisplay.baseUrl
        let parameters = this.buildParameters(false);

        return `${baseUrl}?${parameters.join('&')}`
    }

    buildParameters(hashParameters) {
        const collections = this.getSelectedItemsCollection(this.state)
        let queryParts = []

        if (collections.scopelist.length > 0) {
            queryParts.push(`collections[scopelist]=${collections.scopelist.join(",")}`)
        }

        if (collections.clusterlist.length > 0) {
            queryParts.push(`collections[clusterlist]=${collections.clusterlist.join(",")}`)
        }

        if (this.state.queueStatus !== 'all') {
            queryParts.push(`queue[status]=${this.state.queueStatus}`)
        }

        if (this.state.template !== 'default') {
            queryParts.push(`template=${this.state.template}`)
        }

        if (! hashParameters && this.state.enableQrCode) {
            queryParts.push(`qrcode=1`)
        }

        if (hashParameters) {
            queryParts.push(`hmac=${this.state.hmac}`)
        }

        return queryParts
    }

    buildWebcalldisplayUrl() {
        const baseUrl  = this.props.config.webcalldisplay.baseUrl
        let parameters = this.buildParameters(true);

        return `${baseUrl}?${parameters.join('&')}`
    }

    componentDidUpdate(prevProps, prevState) {
        if (prevState.selectedItems !== this.state.selectedItems
            || prevState.queueStatus !== this.state.queueStatus
        ) {
            this.signParameters(this.state)
        }
    }

    signParameters(state) {
        const collections = this.getSelectedItemsCollection(state)

        let signingData = {
            'section': 'webcalldisplay',
            'parameters': {}
        }

        if (collections.scopelist.length > 0) {
            signingData.parameters.collections = signingData.parameters.collections || {}
            signingData.parameters.collections.scopelist = collections.scopelist.join(",")
        }
        if (collections.clusterlist.length > 0) {
            signingData.parameters.collections = signingData.parameters.collections || {}
            signingData.parameters.collections.clusterlist = collections.clusterlist.join(",")
        }
        if (this.state.queueStatus !== 'all') {
            signingData.parameters.queue = {}
            signingData.parameters.queue.status = this.state.queueStatus
        }

        const signParametersUrl = this.includeurl + '/sign/parameters/'
        fetch(
            signParametersUrl,
            {
                method: 'POST',
                cache: 'no-cache',
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify(signingData)
            }
        ).then(response => response.json()).then(data => this.setState({ hmac: data.hmac }))
    }

    toggleQrCodeView() {
        this.setState({
            showQrCode: !this.state.showQrCode
        });
    }

    renderCheckbox(enabled, onShowChange, label) {
        const onChange = () => onShowChange(!enabled)

        return (
            <Inputs.Checkbox checked={enabled} {...{ onChange, label }} />
        )
    }

    showItem(item) {
        const items = this.state.selectedItems.filter(i => i.id !== item.id || (i.id == item.id && i.type !== item.type))
        const newItem = Object.assign({}, item)
        items.push(newItem)
        this.setState({ selectedItems: items })
    }

    hideItem(item) {
        const items = this.state.selectedItems.filter(i => i.id !== item.id || (i.id == item.id && i.type !== item.type))
        this.setState({ selectedItems: items })
    }

    renderItem(item) {
        const onChange = show => {
            if (show) {
                this.showItem(item)
            } else {
                this.hideItem(item)
            }
        }

        const text = `${item.contact ? item.contact.name : item.name} ${item.shortName ? item.shortName : ""}`
        const prefix = item.type === 'cluster' ? 'Cluster: ' : ''

        const itemEnabled = this.state.selectedItems.reduce((carry, current) => {
            return carry || (current.id === item.id && current.type === item.type)
        }, false)
        return (
            <li tabIndex="-1" role="option" aria-checked={itemEnabled}>
                <div key={item.id} className="form-check ticketprinter-config__item">
                    {this.renderCheckbox(itemEnabled, onChange, prefix + text)}
                </div>
            </li>
        )
    }

    renderQrCodeEnabled() {
        const onChange = () => {
            this.setState({
                enableQrCode: !this.state.enableQrCode
            });
        }
        return (
            <fieldset>
                <legend className="label">QR-Code für Aufrufanzeige</legend>
                <div key="qrcodeEnabled" className="form-check ticketprinter-config__item">
                    {this.renderCheckbox(this.state.enableQrCode, onChange, "QR-Code anzeigen")}
                </div>
            </fieldset>
        )
    }

    renderScopes(scopes) {
        if (scopes.length > 0) {
            return (
                <fieldset>
                    <legend className="label">Standorte</legend>
                    <ul role="listbox" aria-label="Standortliste" className="checkbox-list">
                    {scopes.map(this.renderItem.bind(this))}
                    </ul>
                </fieldset>
            )
        }
    }

    renderClusters(clusters) {
        if (clusters.length > 0) {
            return (
                <fieldset>
                    <legend className="label">Standort­gruppe</legend>
                    <ul role="listbox" aria-label="Standortclusterliste" className="checkbox-list">
                    {clusters.map(this.renderItem.bind(this))}
                    </ul>
                </fieldset>
            )
        }
    }

    renderDepartment(department) {
        return (
            <div key={department.id}>
                <h2 className="block__heading">{department.name}</h2>
                {this.renderScopes(department.scopes)}
                {this.renderClusters(department.clusters)}
            </div>
        )
    }

    render() {
        const calldisplayUrl = this.buildCalldisplayUrl()
        const webcalldisplayUrl = this.buildWebcalldisplayUrl()

        const onQueueStatusChange = (_, value) => {
            this.setState({
                queueStatus: value
            })
        }

        const onTemplateStatusChange = (_, value) => {
            this.setState({
                template: value
            })
        }

        return (
            <form className="form--base form-group calldisplay-config">
                {this.state.departments.map(this.renderDepartment.bind(this))}
                {this.renderQrCodeEnabled()}
                <FormGroup>
                    <Label 
                        attributes={{ "htmlFor": "visibleCalls" }} 
                        value="Angezeigte Aufrufe">
                    </Label>
                    <Controls>
                        <Select
                            options={[{ name: 'Alle', value: 'all' }, { name: "Nur Abholer", value: 'pickup' }, { name: "Spontan- und Terminkunden", value: 'called' }]}
                            value={this.state.queueStatus}
                            attributes={{ "id": "visibleCalls" }}
                            onChange={onQueueStatusChange} />
                    </Controls>
                </FormGroup>
                <FormGroup>
                    <Label attributes={{ "htmlFor": "calldisplayLayout" }} value="Layout"></Label>
                    <Controls>
                        <Select
                            attributes={{ "id": "calldisplayLayout" }}
                            options={[
                                { name: 'Uhrzeit, 6-12 Aufrufe | Platz', value: 'defaultplatz' },
                                { name: 'Uhrzeit, 6-12 Aufrufe | Raum', value: 'defaultraum' },
                                { name: 'Uhrzeit, 6 Aufrufe | Platz', value: 'clock5platz' },
                                { name: 'Uhrzeit, Anzahl Wartende, 6-12 Aufrufe | Platz', value: 'clocknrplatz' },
                                { name: 'Uhrzeit, Anzahl Wartende, 6-12 Aufrufe | Raum', value: 'clocknrraum' },
                                { name: 'Uhrzeit, Anzahl Wartende, Wartezeit, 6-12 Aufrufe | Platz', value: 'clocknrwaitplatz' },
                                { name: 'Uhrzeit, Anzahl Wartende, Wartezeit, 6-12 Aufrufe | Raum', value: 'clocknrwaitraum' },
                                { name: '6-18 Aufrufe | Platz', value: 'raw18platz' }
                            ]}
                            value={this.state.template}
                            onChange={onTemplateStatusChange} />
                    </Controls>
                </FormGroup>
                <FormGroup>
                    <Label attributes={{ "htmlFor": "calldisplayUrl" }} value="URL"></Label>
                    <Controls>
                        <Inputs.Text
                            value={calldisplayUrl}
                            attributes={{ readOnly: true, id: "calldisplayUrl" }} />
                    </Controls>
                </FormGroup>
                <div className="form-actions">
                    <a href={calldisplayUrl} target="_blank" rel="noopener noreferrer" className="button button-submit"><i className="fas fa-external-link-alt" aria-hidden="true"></i> Aktuelle Konfiguration in einem neuen Fenster öffnen</a>
                </div>
                <FormGroup>
                    <Label attributes={{ "htmlFor": "webcalldisplayUrl" }} value="Webcall Display URL"></Label>
                    <Controls>
                        <Inputs.Text
                            value={webcalldisplayUrl}
                            attributes={{ readOnly: true, id: "webcalldisplayUrl" }} />
                    </Controls>
                </FormGroup>
                <div className="form-actions">
                    <button className="button" aria-hidden="true" onClick={(event) => {event.preventDefault(); this.toggleQrCodeView();}}>QR-Code anzeigen / drucken</button>
                    <a href={webcalldisplayUrl} target="_blank" rel="noopener noreferrer" className="button button-submit"><i className="fas fa-external-link-alt" aria-hidden="true"></i> in der mobilen Anzeige öffnen</a>
                </div>
                { this.state.showQrCode ? <QrCodeView text='QrCode für die mobile Ansicht des Aufrufsystems' targetUrl={webcalldisplayUrl} togglePopup={this.toggleQrCodeView.bind(this)} /> : null }
            </form>
        )
    }
}

CallDisplayConfigView.propTypes = {
    includeurl: PropTypes.string,
    departments: PropTypes.array,
    organisation: PropTypes.object,
    config: PropTypes.shape({
        calldisplay: PropTypes.object,
        webcalldisplay: PropTypes.object
    })
}

export default CallDisplayConfigView

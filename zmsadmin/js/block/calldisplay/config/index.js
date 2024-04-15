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
            webtemplate: 'defaultplatz',
            hmac: '',
            enableQrCode: false,
            twoDisplays: false,
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

    buildHost() {
        return document.location.origin;
    }

    buildCalldisplayUrl(displayNumber = 1) {
        const baseUrl  = this.props.config.calldisplay.baseUrl
        let parameters = this.buildParameters(false, 'calldisplay', displayNumber);

        return `${this.buildHost()}${baseUrl}?${parameters.join('&')}`
    }

    buildWebcalldisplayUrl() {
        const baseUrl  = this.props.config.webcalldisplay.baseUrl
        let parameters = this.buildParameters(true, 'webcalldisplay');

        return `${this.buildHost()}${baseUrl}?${parameters.join('&')}`
    }

    buildParameters(hashParameters, target = 'calldisplay', displayNumber = 1) {
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

        if (target == 'calldisplay' && this.state.template !== 'default') {
            queryParts.push(`template=${this.state.template}`)
        }

        if (target == 'webcalldisplay' && this.state.template !== 'default') {
            queryParts.push(`template=${this.state.webtemplate}`)
        }

        if (! hashParameters && this.state.enableQrCode) {
            queryParts.push(`qrcode=1`)
        }

        if (! hashParameters && this.state.twoDisplays) {
            queryParts.push(`display=` + displayNumber)
        }

        if (hashParameters) {
            queryParts.push(`hmac=${this.state.hmac}`)
        }

        return queryParts
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
            <li>
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

    renderTwoDisplays() {
        const onChange = () => {
            this.setState({
                twoDisplays: !this.state.twoDisplays
            });
        }
        return (
            <fieldset>
                <legend className="label">2 Aufrufanzeige</legend>
                <div key="twoDisplays" className="form-check ticketprinter-config__item">
                    {this.renderCheckbox(this.state.twoDisplays, onChange, "2 Aufrufanzeige erstellen")}
                </div>
            </fieldset>
        )
    }

    renderScopes(scopes) {
        if (scopes.length > 0) {
            return (
                <fieldset>
                    <legend className="label">Standorte</legend>
                    <ul aria-label="Standortliste" className="checkbox-list">
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
                    <ul aria-label="Standortclusterliste" className="checkbox-list">
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
        const calldisplayUrl2 = this.buildCalldisplayUrl(2)
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

        const onWebTemplateStatusChange = (_, value) => {
            this.setState({
                webtemplate: value
            })
        }

        return (
            <form className="form--base form-group calldisplay-config">
                {this.state.departments.map(this.renderDepartment.bind(this))}
                {this.renderQrCodeEnabled()}
                {this.renderTwoDisplays()}
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
                    <Label attributes={{ "htmlFor": "calldisplayLayout" }} value="Layout Aufrufanzeige"></Label>
                    <Controls>
                        <Select
                            attributes={{ "id": "calldisplayLayout" }}
                            options={[
                                { name: '10 Aufrufe | Schalter (engl. counter)', value: 'default_counter' },
                                { name: '10 Aufrufe | Platz (engl. site)', value: 'default_platz' },
                                { name: '10 Aufrufe | Tür (engl. door)', value: 'default_tuer' },
                                { name: '10 Aufrufe | Raum (engl. room)', value: 'default_raum' }
                            ]}
                            value={this.state.template}
                            onChange={onTemplateStatusChange} />
                    </Controls>
                </FormGroup>
                <FormGroup>
                    { this.state.twoDisplays ?
                    <Label attributes={{ "htmlFor": "calldisplayUrl" }} value="URL Anzeige 1"></Label>
                        :
                    <Label attributes={{ "htmlFor": "calldisplayUrl" }} value="URL"></Label> }
                    <Controls>
                        <Inputs.Text
                            value={calldisplayUrl}
                            attributes={{ readOnly: true, id: "calldisplayUrl" }} />
                    </Controls>
                </FormGroup>
                <div className="form-actions">
                    <a href={calldisplayUrl} target="_blank" rel="noopener noreferrer" className="button button-submit"><i className="fas fa-external-link-alt"></i> Aktuelle Konfiguration in einem neuen Fenster öffnen</a>
                </div>


                { this.state.twoDisplays ?
                <div className="firstDisplay">
                    <FormGroup>
                        <Label attributes={{ "htmlFor": "calldisplayUrl2" }} value="URL Anzeige 2"></Label>
                        <Controls>
                            <Inputs.Text
                                value={calldisplayUrl2}
                                attributes={{ readOnly: true, id: "calldisplayUrl2" }} />
                        </Controls>
                    </FormGroup>
                    <div className="form-actions">
                        <a href={calldisplayUrl2} target="_blank" rel="noopener noreferrer" className="button button-submit"><i className="fas fa-external-link-alt"></i> Aktuelle Konfiguration in einem neuen Fenster öffnen</a>
                    </div>
                </div>
                    : null }

                <FormGroup>
                    <Label attributes={{ "htmlFor": "webcalldisplayLayout" }} value="Layout mobile Aufrufanzeige"></Label>
                    <Controls>
                        <Select
                            attributes={{ "id": "webcalldisplayLayout" }}
                            options={[
                                { name: 'Uhrzeit, 6-12 Aufrufe | Platz', value: 'defaultplatz' },
                                { name: 'Uhrzeit, 6-12 Aufrufe | Raum', value: 'defaultraum' },
                                { name: 'Uhrzeit, Anzahl Wartende, 6-12 Aufrufe | Platz', value: 'nrwaitplatz' },
                                { name: 'Uhrzeit, Anzahl Wartende, 6-12 Aufrufe | Raum', value: 'nrwaitraum' },
                                { name: 'Legacy', value: 'legacy' },
                                { name: 'Lokal', value: 'local' },
                                { name: 'Allgemein', value: 'usual' },
                            ]}
                            value={this.state.webtemplate}
                            onChange={onWebTemplateStatusChange} />
                    </Controls>
                </FormGroup>
                <FormGroup>
                    <Label attributes={{ "htmlFor": "webcalldisplayUrl" }} value="Webcall Display URL"></Label>
                    <Controls>
                        <Inputs.Text
                            value={webcalldisplayUrl}
                            attributes={{ readOnly: true, id: "webcalldisplayUrl" }} />
                    </Controls>
                </FormGroup>
                <div className="form-actions">
                    <button className="button" onClick={(event) => {event.preventDefault(); this.toggleQrCodeView();}}>QR-Code anzeigen / drucken</button>
                    <a href={webcalldisplayUrl} target="_blank" rel="noopener noreferrer" className="button button-submit"><i className="fas fa-external-link-alt"></i> in der mobilen Anzeige öffnen</a>
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
        calldisplay: PropTypes.shape({
            baseUrl: PropTypes.object
        }),
        webcalldisplay: PropTypes.shape({
            baseUrl: PropTypes.object
        })
    })
}

export default CallDisplayConfigView

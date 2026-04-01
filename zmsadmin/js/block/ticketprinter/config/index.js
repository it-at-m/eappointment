import React, { Component } from 'react'
import PropTypes from 'prop-types'
import * as Inputs from '../../../lib/inputs'
import accordion from 'bo-layout-admin-js/behavior/accordion'
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
        services: services ? services.map(readPropsService) : []
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
            homeUrl: "",
            template: "default",
            ticketPrinterName: "",
            enableAdvancedConfig: false,
            defaultLanguage: "de",
            languages: ["de"],
            serviceConfig: {},
            expandedLanguages: { de: true }
        }
    }

    componentDidMount() {
        accordion()
    }

    componentDidUpdate(prevProps, prevState) {
        if (!prevState.enableAdvancedConfig && this.state.enableAdvancedConfig) {
            accordion()
        }
    }

    toggleAdvancedConfig(value) {
        this.setState({
            enableAdvancedConfig: value
        })
    }

    toggleLanguageExpanded(language) {
        this.setState(prevState => ({
            expandedLanguages: {
                ...prevState.expandedLanguages,
                [language]: !prevState.expandedLanguages[language]
            }
        }))
    }

    addLanguage() {
        let nextIndex = this.state.languages.length + 1
        let newLanguage = `lang${nextIndex}`

        while (this.state.languages.includes(newLanguage)) {
            nextIndex++
            newLanguage = `lang${nextIndex}`
        }

        this.setState(prevState => ({
            languages: [...prevState.languages, newLanguage],
            expandedLanguages: {
                ...prevState.expandedLanguages,
                [newLanguage]: true
            },
            serviceConfig: {
                ...prevState.serviceConfig,
                [newLanguage]: {}
            }
        }))
    }

    updateLanguage(index, value) {
        const normalizedValue = (value || '').trim().toLowerCase()
        const languages = [...this.state.languages]
        const previousValue = languages[index]

        const isDuplicate = languages.some((lang, i) => i !== index && lang === normalizedValue)
        if (isDuplicate) {
            return
        }

        languages[index] = normalizedValue

        this.setState(prevState => {
            const serviceConfig = { ...prevState.serviceConfig }
            const expandedLanguages = { ...prevState.expandedLanguages }

            if (previousValue !== normalizedValue) {
                if (serviceConfig[previousValue]) {
                    serviceConfig[normalizedValue] = serviceConfig[previousValue]
                    delete serviceConfig[previousValue]
                }
                if (expandedLanguages[previousValue] !== undefined) {
                    expandedLanguages[normalizedValue] = expandedLanguages[previousValue]
                    delete expandedLanguages[previousValue]
                }
            }

            return {
                languages,
                serviceConfig,
                expandedLanguages,
                defaultLanguage: prevState.defaultLanguage === previousValue ? normalizedValue : prevState.defaultLanguage
            }
        })
    }

    removeLanguage(index) {
        const languageToRemove = this.state.languages[index]
        const languages = this.state.languages.filter((_, i) => i !== index)

        if (!languages.length) {
            return
        }

        this.setState(prevState => {
            const serviceConfig = { ...prevState.serviceConfig }
            delete serviceConfig[languageToRemove]

            const expandedLanguages = { ...prevState.expandedLanguages }
            delete expandedLanguages[languageToRemove]

            return {
                languages,
                serviceConfig,
                expandedLanguages,
                defaultLanguage: prevState.defaultLanguage === languageToRemove ? languages[0] : prevState.defaultLanguage
            }
        })
    }

    getSelectedServices() {
        return this.state.selectedItems.filter(item => item.type === 'r')
    }

    getServiceId(item) {
        const parts = String(item.id || '').split('-')
        return parts.length > 1 ? parts[1] : item.id
    }

    getServiceConfigValue(language, serviceId, field) {
        return (
            (((this.state.serviceConfig || {})[language] || {})[serviceId] || {})[field] || ''
        )
    }

    updateServiceConfig(language, serviceId, field, value) {
        this.setState(prevState => {
            const serviceConfig = { ...prevState.serviceConfig }
            const languageConfig = { ...(serviceConfig[language] || {}) }
            const serviceEntry = {
                ...(languageConfig[serviceId] || {
                    label: '',
                    customText1: '',
                    customText2: ''
                })
            }

            serviceEntry[field] = value
            languageConfig[serviceId] = serviceEntry
            serviceConfig[language] = languageConfig

            return { serviceConfig }
        })
    }

    buildAdvancedConfig() {
        if (!this.state.enableAdvancedConfig) {
            return null
        }

        const selectedServiceIds = this.getSelectedServices().map(item => this.getServiceId(item))

        const languages = this.state.languages
            .filter(language => language.trim() !== '')
            .map(language => {
                const langConfig = (this.state.serviceConfig[language] || {})

                const services = {}
                selectedServiceIds.forEach(serviceId => {
                    const entry = langConfig[serviceId] || {}
                    const { label = '', customText1 = '', customText2 = '' } = entry

                    if (label || customText1 || customText2) {
                        services[serviceId] = { label, customText1, customText2 }
                    }
                })

                return { language, services }
            })
            
        return {
            defaultLanguage: this.state.defaultLanguage,
            languages
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

        const config = this.buildAdvancedConfig()
        if (config) {
            const encoded = btoa(encodeURIComponent(JSON.stringify(config)).replace(/%([0-9A-F]{2})/g, (_, p1) => String.fromCharCode(parseInt(p1, 16))))
            parameters.push(`config=${encoded}`)
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

    renderLanguageConfig() {
        const onDefaultLanguageChange = (_, value) => {
            this.setState({ defaultLanguage: value })
        }

        return (
            <div className="ticketprinter-config__languages">
                <FormGroup key="ticketprinter-default-language">
                    <Label attributes={{ "htmlFor": "ticketprinterDefaultLanguage" }} value="Standardsprache" />
                    <Controls>
                        <Select
                            attributes={{ "id": "ticketprinterDefaultLanguage" }}
                            options={this.state.languages.map(language => ({
                                name: language || '(leer)',
                                value: language
                            }))}
                            value={this.state.defaultLanguage}
                            onChange={onDefaultLanguageChange}
                        />
                    </Controls>
                </FormGroup>

                <fieldset>
                    <legend className="label">Sprachen</legend>

                    {this.state.languages.map((language, index) => {
                        const isExpanded = !!this.state.expandedLanguages[language]
                        return (
                            <div key={`language-${index}`} className="accordion">
                                <div className="accordion__heading ticketprinter-config__language-header" role="heading" aria-level="3">
                                    <button
                                        type="button"
                                        className="language-accordion__trigger"
                                        aria-expanded={isExpanded ? "true" : "false"}
                                        onClick={() => this.toggleLanguageExpanded(language)}
                                    >
                                        {language || `Sprache ${index + 1}`}
                                    </button>
                                    <button
                                        type="button"
                                        className="ticketprinter-config__remove-language"
                                        onClick={() => this.removeLanguage(index)}
                                        disabled={this.state.languages.length === 1}
                                        title="Sprache entfernen"
                                    >
                                        <i className="fas fa-times" />
                                    </button>
                                </div>

                                {isExpanded && (
                                    <div className="accordion__panel opened">
                                        <div className="ticketprinter-config__language-input">
                                            <Inputs.Text
                                                value={language}
                                                attributes={{ "id": `ticketprinterLanguage-${index}` }}
                                                onChange={(name, value) => this.updateLanguage(index, value)}
                                            />
                                        </div>
                                        {this.getSelectedServices().length === 0 ? (
                                            <p>Bitte oben mindestens eine Dienstleistung auswählen.</p>
                                        ) : (
                                            this.getSelectedServices().map(item =>
                                                this.renderServiceTextFields(language, item)
                                            )
                                        )}
                                    </div>
                                )}
                            </div>
                        )
                    })}

                    <div className="form-actions">
                        <button
                            type="button"
                            className="button button--default"
                            onClick={() => this.addLanguage()}
                        >
                            Sprache hinzufügen
                        </button>
                    </div>
                </fieldset>
            </div>
        )
    }

    renderServiceTextFields(language, item) {
        const serviceId = this.getServiceId(item)
        const serviceName = item.name || serviceId

        return (
            <fieldset key={`${language}-${serviceId}`} className="ticketprinter-config__service-fields">
                <legend className="label">{serviceName}</legend>

                <FormGroup key={`${language}-${serviceId}-label`}>
                    <Label
                        attributes={{ "htmlFor": `service-label-${language}-${serviceId}` }}
                        value="Bezeichnung"
                    />
                    <Controls>
                        <Inputs.Text
                            value={this.getServiceConfigValue(language, serviceId, 'label')}
                            attributes={{ "id": `service-label-${language}-${serviceId}` }}
                            onChange={(name, value) => this.updateServiceConfig(language, serviceId, 'label', value)}
                        />
                    </Controls>
                </FormGroup>

                <FormGroup key={`${language}-${serviceId}-customText1`}>
                    <Label
                        attributes={{ "htmlFor": `service-customText1-${language}-${serviceId}` }}
                        value="Custom Text 1"
                    />
                    <Controls>
                        <Inputs.Text
                            value={this.getServiceConfigValue(language, serviceId, 'customText1')}
                            attributes={{ "id": `service-customText1-${language}-${serviceId}` }}
                            onChange={(name, value) => this.updateServiceConfig(language, serviceId, 'customText1', value)}
                        />
                    </Controls>
                </FormGroup>

                <FormGroup key={`${language}-${serviceId}-customText2`}>
                    <Label
                        attributes={{ "htmlFor": `service-customText2-${language}-${serviceId}` }}
                        value="Custom Text 2"
                    />
                    <Controls>
                        <Inputs.Text
                            value={this.getServiceConfigValue(language, serviceId, 'customText2')}
                            attributes={{ "id": `service-customText2-${language}-${serviceId}` }}
                            onChange={(name, value) => this.updateServiceConfig(language, serviceId, 'customText2', value)}
                        />
                    </Controls>
                </FormGroup>
            </fieldset>
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
                    <FormGroup key="advanced-config-group" className="ticketprinter-config__advanced-group">
                        <div className="label">Erweiterte Konfiguration</div>

                        <div className="ticketprinter-config__advanced-content">
                            <label className="label">
                                <input
                                    type="checkbox"
                                    className="ticketprinter-config__advanced-checkbox"
                                    checked={this.state.enableAdvancedConfig}
                                    onChange={(ev) => this.toggleAdvancedConfig(ev.target.checked)}
                                />
                                Erweiterte Sprach- und Textkonfiguration verwenden
                            </label>

                            {this.state.enableAdvancedConfig && (
                                <dl
                                    id="ticketprinter-config-accordion"
                                    className="accordion js-accordion"
                                    data-allow-multiple="false"
                                    data-allow-toggle="true"
                                >
                                    <dt className="accordion__heading">
                                        <button
                                            type="button"
                                            className="accordion__trigger"
                                            aria-expanded="false"
                                        >
                                            Sprach- und Textkonfiguration
                                        </button>
                                    </dt>
                                    <dd className="accordion__panel">
                                        {this.renderLanguageConfig()}
                                    </dd>
                                </dl>
                            )}
                        </div>
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

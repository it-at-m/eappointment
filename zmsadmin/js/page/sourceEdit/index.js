import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { deepMerge, makeNestedObj, getFieldList } from '../../lib/utils'
import MandantView from '../../block/source/mandant'
import RequestsView from '../../block/source/requests'
import ProvidersView from '../../block/source/providers'
import RequestRelationView from '../../block/source/requestrelations'

class SourceView extends Component {
    constructor(props) {
        super(props)
        this.changeHandler = this.changeHandler.bind(this)
        this.addNewHandler = this.addNewHandler.bind(this)
        this.deleteHandler = this.deleteHandler.bind(this)
    }

    changeHandler(field, value) {
        let newstate = this.props.source
        const fieldList = getFieldList(field)
        if (fieldList.length === 1) {
            newstate[fieldList.pop()] = value
        } else {
            newstate = deepMerge(newstate, makeNestedObj(fieldList, value))
        }
        this.setState({ source: newstate });
    }

    addNewHandler(field, props) {
        let newstate = this.props.source
        newstate[field] = this.props.source[field].concat(props)
        this.setState({ source: newstate })
    }

    getDeleteLabel(field, index) {
        const src = this.props.source || {};

        if (field === 'requests') {
            const r = (src.requests || [])[index];
            return `„${r && r.name ? r.name : 'Dienstleistung'}“`;
        }

        if (field === 'providers') {
            const p = (src.providers || [])[index];
            return `„${p && p.name ? p.name : 'Dienstleister'}“`;
        }

        if (field === 'requestrelation') {
            const rel = (src.requestrelation || [])[index] || {};
            const reqId = rel.request && rel.request.id;
            const prvId = rel.provider && rel.provider.id;

            const req = (src.requests || []).find(r => String(r.id) === String(reqId));
            const prv = (src.providers || []).find(p => String(p.id) === String(prvId));

            const rName = (req && req.name) || (rel.request && rel.request.name) || 'Dienstleistung';
            const pName = (prv && prv.name) || (rel.provider && rel.provider.name) || 'Dienstleister';

            return `Kombination „${rName} × ${pName}“`;
        }

        return 'diesen Datensatz';
    }

    deleteHandler(field, deleteIndex) {
        const label = this.getDeleteLabel(field, deleteIndex);
        const msg = `${label} wirklich löschen?\n\nHinweis: Die Änderung wird erst nach „Speichern“ wirksam.`;
        if (!window.confirm(msg)) return;

        let newstate = this.props.source;
        newstate[field] = list.filter((_, i) => i !== deleteIndex);
        this.setState({ source: newstate });
    }

    componentDidMount() {
        //console.log("mounted source component")
    }

    componentDidUpdate() {
        //console.log("updated source component")
    }

    render() {
        return (
            <div>
                <MandantView
                    {...this.props}
                    source={this.props.source}
                    changeHandler={this.changeHandler}
                />
                <fieldset>
                    <legend>Dienstleister</legend>
                    <ProvidersView
                        {...this.props}
                        parentproviders={this.props.parentproviders}
                        source={this.props.source}
                        changeHandler={this.changeHandler}
                        addNewHandler={this.addNewHandler}
                        deleteHandler={this.deleteHandler}
                    />
                </fieldset>
                <fieldset>
                    <legend>Dienstleistungen</legend>
                    <RequestsView
                        {...this.props}
                        source={this.props.source}
                        parentrequests={this.props.parentrequests}
                        requestvariants={this.props.requestvariants}
                        changeHandler={this.changeHandler}
                        addNewHandler={this.addNewHandler}
                        deleteHandler={this.deleteHandler}
                    />
                </fieldset>
                <fieldset>
                    <legend>Kombinationen</legend>
                    <RequestRelationView
                        {...this.props}
                        source={this.props.source}
                        changeHandler={this.changeHandler}
                        addNewHandler={this.addNewHandler}
                        deleteHandler={this.deleteHandler}
                    />
                </fieldset>
            </div>
        );
    }
}

SourceView.propTypes = {
    source: PropTypes.object,
    parentProviders: PropTypes.array.isRequired,
    parentrequests: PropTypes.array.isRequired,
}

export default SourceView

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
        this.state = { source: props.source }
        this.changeHandler = this.changeHandler.bind(this)
        this.addNewHandler = this.addNewHandler.bind(this)
        this.deleteHandler = this.deleteHandler.bind(this)
    }

    changeHandler(field, value) {
        this.setState((prevState) => {
            let newstate = prevState.source
            const fieldList = getFieldList(field)
            if (fieldList.length === 1) {
                newstate[fieldList.pop()] = value
            } else {
                newstate = deepMerge(newstate, makeNestedObj(fieldList, value))
            }
            return { source: newstate }
        })
    }

    addNewHandler(field, props) {
        this.setState((prevState) => {
            const newstate = prevState.source
            newstate[field] = prevState.source[field].concat(props)
            return { source: newstate }
        })
    }

    deleteHandler(field, deleteIndex) {
        this.setState((prevState) => {
            const newstate = prevState.source
            newstate[field] = prevState.source[field].filter((item, index) => index !== deleteIndex)
            return { source: newstate }
        })
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
                    source={this.state.source}
                    changeHandler={this.changeHandler}
                />
                <fieldset>
                    <legend>Dienstleister</legend>
                    <ProvidersView
                        {...this.props}
                        parentproviders={this.props.parentproviders}
                        source={this.state.source}
                        changeHandler={this.changeHandler}
                        addNewHandler={this.addNewHandler}
                        deleteHandler={this.deleteHandler}
                    />
                </fieldset>
                <fieldset>
                    <legend>Dienstleistungen</legend>
                    <RequestsView
                        {...this.props}
                        source={this.state.source}
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
                        source={this.state.source}
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

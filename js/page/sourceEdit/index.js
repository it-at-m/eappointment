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

    deleteHandler(field, deleteIndex) {
        let newstate = this.props.source
        newstate[field] = this.props.source[field].filter((item, index) => {
            return index !== deleteIndex
        })
        this.setState({ source: newstate })
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
                    <legend>Dienstleistungen</legend>
                    <RequestsView
                        {...this.props}
                        source={this.props.source}
                        changeHandler={this.changeHandler}
                        addNewHandler={this.addNewHandler}
                        deleteHandler={this.deleteHandler}
                    />
                </fieldset>
                <fieldset>
                    <legend>Dienstleister</legend>
                    <ProvidersView
                        {...this.props}
                        source={this.props.source}
                        changeHandler={this.changeHandler}
                        addNewHandler={this.addNewHandler}
                        deleteHandler={this.deleteHandler}
                    />
                </fieldset>
                <fieldset>
                    <legend>Zeitslots</legend>
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
    source: PropTypes.object
}

export default SourceView

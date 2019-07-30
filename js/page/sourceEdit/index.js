import React, { Component, PropTypes } from 'react'
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
        this.state = this.props
    }

    changeHandler(field, value) {
        let newstate = this.state.source
        const fieldList = getFieldList(field)
        if (fieldList.length === 1) {
            newstate[fieldList.pop()] = value
        } else {
            newstate = deepMerge(newstate, makeNestedObj(fieldList, value))
        }
        this.setState({ source: newstate });
    }

    addNewHandler(field, props) {
        let newstate = this.state.source
        newstate[field] = this.state.source[field].concat(props)
        this.setState({ source: newstate })
    }

    deleteHandler(field, deleteIndex) {
        let newstate = this.state.source
        newstate[field] = this.state.source[field].filter((item, index) => {
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
                    source={this.state.source}
                    changeHandler={this.changeHandler}
                />
                <fieldset>
                    <legend>Dienstleistungen</legend>
                    <div className="panel--heavy">
                    <RequestsView
                        {...this.props}
                        source={this.state.source}
                        changeHandler={this.changeHandler}
                        addNewHandler={this.addNewHandler}
                        deleteHandler={this.deleteHandler}
                    />
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Dienstleister</legend>
                    <div className="panel--heavy">
                    <ProvidersView
                        {...this.props}
                        source={this.state.source}
                        changeHandler={this.changeHandler}
                        addNewHandler={this.addNewHandler}
                        deleteHandler={this.deleteHandler}
                    />
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Zeitslots</legend>
                    <div className="panel--heavy">
                    <RequestRelationView
                        {...this.props}
                        source={this.state.source}
                        changeHandler={this.changeHandler}
                        addNewHandler={this.addNewHandler}
                        deleteHandler={this.deleteHandler}
                    />
                    </div>
                </fieldset>
            </div>
        );
    }
}

SourceView.propTypes = {
    requests: PropTypes.array,
    labels: PropTypes.array.isRequired,
    descriptions: PropTypes.array.isRequired,
    source: PropTypes.array.isRequired
}

export default SourceView

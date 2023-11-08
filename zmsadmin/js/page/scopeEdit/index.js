import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { deepMerge, makeNestedObj, getFieldList } from '../../lib/utils'
import { getEntity } from '../../lib/schema'
import SourceView from '../../block/scope/sourcesselectform'
import $ from "jquery"

class ScopeView extends Component {
    constructor(props) {
        super(props)
        this.changeHandler = this.changeHandler.bind(this)
        this.onChangeSourceHandler = this.onChangeSourceHandler.bind(this)
        this.state = this.props
    }

    changeHandler(field, value) {
        let newstate = this.state.scopestate
        const fieldList = getFieldList(field)
        if (fieldList.length === 1) {
            newstate[fieldList.pop()] = value
        } else {
            newstate = deepMerge(newstate, makeNestedObj(fieldList, value))
        }
        this.setState({ scopestate: newstate });
    }

    onChangeSourceHandler(field, value) {
        $.ajax(`${this.props.includeurl}/provider/${value}/`, {
            method: 'GET'
        }).done((success) => {
            this.changeHandler(field, success)
            getEntity('provider').then((entity) => {
                this.changeHandler('provider', entity)
            })
        }).fail((err) => {
            if (err.status === 404) {
                console.log('404 error, ignored')
            } else {
                console.log('error', err)
            }
        })
    }

    componentDidMount() {
        //console.log("mounted scopeEdit component", this.state)
    }

    componentDidUpdate() {
        //console.log("updated source component", this.state)
    }

    render() {
        return (
            <div>
                <SourceView
                    {...this.props}
                    changeHandler={this.changeHandler}
                    onChangeSourceHandler={this.onChangeSourceHandler}
                />
            </div>
        );
    }
}

ScopeView.propTypes = {
    includeurl: PropTypes.string.isRequired
}

export default ScopeView

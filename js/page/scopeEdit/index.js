import React, { Component } from 'react'
import { deepMerge, makeNestedObj, getFieldList } from '../../lib/utils'
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
            this.changeHandler('provider', success.find(group => group.name === 'assigned').items[0])

        }).fail((err) => {
            if (err.status === 404) {
                console.log('404 error, ignored')
            } else {
                console.log('error', err)
            }
        })
    }

    componentDidMount() {
        console.log("mounted scopeEdit component")
    }

    componentDidUpdate() {
        console.log("updated source component", this.state)
    }

    render() {
        return (
            <fieldset>
                <SourceView
                    {...this.props}
                    changeHandler={this.changeHandler}
                    onChangeSourceHandler={this.onChangeSourceHandler}
                />
            </fieldset>
        );
    }
}

export default ScopeView

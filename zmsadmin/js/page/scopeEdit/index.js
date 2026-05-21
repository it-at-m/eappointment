import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { deepMerge, makeNestedObj, getFieldList } from '../../lib/utils'
import { getEntity } from '../../lib/schema'
import SourceSuperuserSelectView from '../../block/scope/sourcesSuperuserSelectForm'
import SourceDetailsView from '../../block/scope/sourcesDetailsForm'
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

    render() {
        const props = {
            labels: this.props.labels,
            scopestate: this.state.scopestate,
            permissions: this.props.permissions,
        }

        return (
            <fieldset className="panel--heavy">
                <SourceSuperuserSelectView
                    {...props}
                    changeHandler={this.changeHandler}
                    onChangeSourceHandler={this.onChangeSourceHandler}
                />
                <SourceDetailsView {...props} standalone={false} />
            </fieldset>
        );
    }
}

ScopeView.propTypes = {
    includeurl: PropTypes.string.isRequired
}

export default ScopeView

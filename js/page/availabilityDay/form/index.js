import React, { Component } from 'react'
import PropTypes from 'prop-types'
import HeaderButtons from './headerButtons'
import FormContent from './content'
import { getDataValuesFromForm, cleanupFormData, getFormValuesFromData } from '../helpers'

class AvailabilityForm extends Component {
    constructor(props) {
        super(props);
        
        this.state = {
            data: getFormValuesFromData(this.props.data),
            errors: {}
        };
        this.handleFocus = props.handleFocus
    }

    componentDidUpdate(prevProps) {
        if (this.props.data !== prevProps.data) {
            this.setState({
                data: getFormValuesFromData(this.props.data)
            })
        }
    }

    handleChange(name, value) {
        this.setState({
            data: cleanupFormData(Object.assign({}, this.state.data, {
                [name]: value,
                __modified: true
            }))
        }, () => {
            this.props.onChange(getDataValuesFromForm(this.state.data, this.props.data.scope))
        })
    }

    render() {
        const { data, errors } = this.state
        const onChange = (name, value) => {
            this.handleChange(name, value)
        }

        return (
            <div>
                {<HeaderButtons 
                    onCopy={this.props.onCopy} 
                    onException={this.props.onException}
                    onEditInFuture={this.props.onEditInFuture} 
                />}
                {<FormContent {... { data, errors, onChange }} />}
            </div>
        )   
    }
}

AvailabilityForm.defaultProps = {
    data: {},
    onChange: () => { },
    onCopy: () => { },
    onException: () => { },
    onEditInFuture: () => { }
}

AvailabilityForm.propTypes = {
    data: PropTypes.object,
    onChange: PropTypes.func,
    onCopy: PropTypes.func,
    onException: PropTypes.func,
    onEditInFuture: PropTypes.func,
    handleFocus: PropTypes.func
}

export default AvailabilityForm

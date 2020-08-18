import React, { Component } from 'react'
import PropTypes from 'prop-types'
import FormButtons from './formButtons'
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
            this.props.handleChange(getDataValuesFromForm(this.state.data, this.props.data.scope))
        })
    }

    render() {
        const { data, errors } = this.state
        const onChange = (name, value) => {
            this.handleChange(name, value)
        }

        return (
            <div>
                {<FormContent {... { data, errors, onChange }} />}
                {<FormButtons 
                    data = {data}
                    onCopy={this.props.onCopy} 
                    onExclusion={this.props.onExclusion}
                    onEditInFuture={this.props.onEditInFuture} 
                />}
            </div>
        )   
    }
}

AvailabilityForm.defaultProps = {
    data: {},
    handleChange: () => { },
    onCopy: () => { },
    onExclusion: () => { },
    onEditInFuture: () => { }
}

AvailabilityForm.propTypes = {
    data: PropTypes.object,
    handleChange: PropTypes.func,
    onCopy: PropTypes.func,
    onExclusion: PropTypes.func,
    onEditInFuture: PropTypes.func,
    handleFocus: PropTypes.func
}

export default AvailabilityForm

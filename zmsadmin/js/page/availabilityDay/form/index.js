import React, { Component } from 'react'
import PropTypes from 'prop-types'
import FormButtons from './formButtons'
import FormContent from './content'
import { getDataValuesFromForm, cleanupFormData, getFormValuesFromData } from '../helpers'
import { hasSlotCountError } from '../form/validate';

class AvailabilityForm extends Component {
    constructor(props) {
        super(props);
        this.state = {
            data: getFormValuesFromData(this.props.data)
        };
    }

    componentDidUpdate(prevProps) {
        if (this.props.data && this.props.data !== prevProps.data) {
            this.setState({
                data: getFormValuesFromData(this.props.data)
            })
        }
    }

    handleChange(name, value) {
        this.setState((state) => ({
            data: cleanupFormData(Object.assign({}, state.data, {
                [name]: value,
                __modified: true
            }))
        }), () => {
            this.props.handleChange(getDataValuesFromForm(this.state.data, this.props.data.scope))
        })
    }

    render() {
        const { data } = this.state
        const onChange = (name, value) => {
            this.handleChange(name, value)
        }

        const hasConflicts = ((
                this.props.conflictList.itemList && 
                Object.keys(this.props.conflictList.itemList).length
            ) || 
            (
                this.props.errorList &&
                Object.keys(this.props.errorList).length
        )) ? true : false

        return (
            <div>
                {<FormContent 
                    today = {this.props.today} 
                    availabilityList={this.props.availabilityList}
                    setErrorRef={this.props.setErrorRef}
                    errorList={this.props.errorList}
                    conflictList={this.props.conflictList}
                    {... { data, onChange }} />}
                {<FormButtons 
                    data = {data}
                    onCopy={this.props.onCopy} 
                    onExclusion={this.props.onExclusion}
                    onEditInFuture={this.props.onEditInFuture} 
                    onDelete={this.props.onDelete}
                    onUpdateSingle={this.props.onUpdateSingle}
                    selectedDate={this.props.selectedDate}
                    hasConflicts={hasConflicts}
                    hasSlotCountError={hasSlotCountError(this.props)}
                    isCreatingExclusion={this.props.isCreatingExclusion}
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
    availabilityList: PropTypes.array,
    errorList: PropTypes.object,
    conflictList: PropTypes.object,
    data: PropTypes.object,
    today: PropTypes.number,
    selectedDate: PropTypes.number,
    handleChange: PropTypes.func,
    onCopy: PropTypes.func,
    onExclusion: PropTypes.func,
    onEditInFuture: PropTypes.func,
    setErrorRef: PropTypes.func,
    onDelete: PropTypes.func,
    onUpdateSingle: PropTypes.func,
    isCreatingExclusion: PropTypes.bool
}

export default AvailabilityForm

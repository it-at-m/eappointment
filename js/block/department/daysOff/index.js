import React, { Component, PropTypes } from 'react'

import * as Inputs from '../../../lib/inputs'

const renderDay = (day, index, onChange, onDeleteClick) => {
    const formName = `dayoff[${index}]`

    const onChangeName = (_, value) => onChange(index, 'name', value)
    const onChangeDate = (_, value) => onChange(index, 'date', value)

    return (
        <div className="daysoff-item">
            <div className="daysoff-item__name">
                <Inputs.Text
                    name={`${formName}[name]`}
                    placeholder="Name"
                    value={day.name}
                    onChange={onChangeName}
                />
            </div>
            <div className="daysoff-item__date">
                <Inputs.Date
                    name={`${formName}[date]`}
                    placeholder="Datum"
                    value={day.date}
                    onChange={onChangeDate}
                />
            </div>
            <div className="daysoff-item__delete">
                <label className="checkboxdeselect daysoff__delete-button">
                    <input type="checkbox" checked={true} onClick={() => onDeleteClick(index)} /><span>Löschen</span>
                </label>
            </div>
        </div>
    )
}

class DaysOffView extends Component {
    constructor(props) {
        super(props)

        this.state = {
            days: props.days.length > 0 ? props.days : [{ name: '', date: Date.now() / 1000}]
        }
    }

    changeItemField(index, field, value) {
        console.log('change item field', index, field, value)
        this.setState({
            days: this.state.days.map((day, dayIndex) => {
                return index === dayIndex ? Object.assign({}, day, { [field]: value }) : day
            })
        })
    }

    addNewItem() {
        const newDate = (new Date()).setFullYear(this.props.year)
        this.setState({
            days: this.state.days.concat([{ name: '', date: newDate / 1000 }])
        })
    }

    deleteItem(deleteIndex) {
        this.setState({
            days: this.state.days.filter( (day, index) => {
                return index !== deleteIndex
            })
        })
    }

    render() {
        console.log('DaysOffView::render', this.state)

        const onNewClick = ev => {
            ev.preventDefault()
            this.addNewItem()
        }

        const onDeleteClick = index => {
            this.deleteItem(index)
        }

        const onChange = (index, field, value) => {
            this.changeItemField(index, field, value)
        }

        return (
            <div className="form-group daysoff">
                <label className="label">Tage:</label>
                <div className="controls">
                    <div className="daysoff__list">
                    {this.state.days.map((day, index) => renderDay(day, index, onChange, onDeleteClick))}
                    </div>
                    <button className="button-default" onClick={onNewClick} >Neuer freier Tag</button>
                </div>
            </div>
        )
    }
}

DaysOffView.defaultProps = {
    year: (new Date()).getFullYear()
}

DaysOffView.propTypes = {
    days: PropTypes.array,
    year: PropTypes.number.isRequired
}

export default DaysOffView

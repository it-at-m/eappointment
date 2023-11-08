import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { getEntity } from '../../../lib/schema'
import * as Inputs from '../../../lib/inputs'
import Datepicker from '../../../lib/inputs/date'

const renderDay = (day, index, onChange, onDeleteClick) => {
    const formName = `dayoff[${index}]`
    const onChangeName = (_, value) => onChange(index, 'name', value)
    const onChangeDate = (value) => onChange(index, 'date', value)
    const onDelete = ev => {
        ev.preventDefault()
        onDeleteClick(index)
    };

    const tdStyle = {
        verticalAlign: "middle",
        height: "0"
      };
    
    return (
        <tr className="daysoff-item" key={index}>
            <td className="daysoff-item__name">
                <Inputs.Text
                    name={`${formName}[name]`}
                    value={day.name}
                    placeholder="Name"
                    onChange={onChangeName}
                    attributes={{ "aria-label": "Bezeichnung" }}
                />
            </td>
            <td className="daysoff-item__date">
                <Datepicker
                    name={`${formName}[date]`}
                    value={day.date}
                    onChange={onChangeDate}
                    attributes={{ "aria-label": "Datum" }}
                />
            </td>
            <td className="daysoff-item__delete" style={tdStyle}>
                <div>
                    <a href="#" className="icon" title="Tag entfernen" aria-label="Tag entfernen" onClick={onDelete}>
                        <i className="far fa-trash-alt" aria-hidden="true"></i>
                    </a>
                </div>
            </td>
        </tr>
    )
}

class DaysOffView extends Component {
    constructor(props) {
        super(props)
        this.state = { days: [] }
    }

    componentDidMount() {
        getEntity('dayoff').then((entity) => {
            entity.date = Date.now() / 1000
            this.setState({
                days: this.props.days.length > 0 ? this.props.days : [entity]
            })
        })
    }

    changeItemField(index, field, value) {
        //console.log('change item field', index, field, value)
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
            days: this.state.days.filter((day, index) => {
                return index !== deleteIndex
            })
        })
    }

    render() {
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
            <div className="daysoff table-responsive-wrapper">
                <div className="table-action-link">
                    <button className="link button-default" onClick={onNewClick} >
                        <i className="fas fa-plus-square color-positive"></i> Neuer freier Tag
                    </button>
                </div>
                <table className="table--base clean">
                    <thead>
                        <tr>
                            <th>Bezeichnung</th>
                            <th>Datum</th>
                            <th>Löschen</th>
                        </tr>
                    </thead>
                    <tbody>
                        {this.state.days.map((day, index) => renderDay(day, index, onChange, onDeleteClick))}
                    </tbody>
                </table>
                <div className="table-action-link">
                    <button className="link button-default" onClick={onNewClick} >
                        <i className="fas fa-plus-square color-positive"></i> Neuer freier Tag
                    </button>
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
    year: PropTypes.number
}

export default DaysOffView

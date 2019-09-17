import React, { Component, PropTypes } from 'react'
import { getEntity } from '../../../lib/schema'
import * as Inputs from '../../../lib/inputs'

const renderDay = (day, index, onChange, onDeleteClick) => {
    const formName = `dayoff[${index}]`

    const onChangeName = (_, value) => onChange(index, 'name', value)
    const onChangeDate = (_, value) => onChange(index, 'date', value)

    return (
        <tr className="daysoff-item">
            <td className="daysoff-item__name">
                <Inputs.Text
                    name={`${formName}[name]`}
                    placeholder="Name"
                    value={day.name}
                    onChange={onChangeName}
                />
            </td>
            <td className="daysoff-item__date">
                <Inputs.Date
                    name={`${formName}[date]`}
                    placeholder="Datum"
                    value={day.date}
                    onChange={onChangeDate}
                />
            </td>
            <td className="daysoff-item__delete">
                <label className="checkboxdeselect daysoff__delete-button">
                    <input type="checkbox" checked={true} onClick={() => onDeleteClick(index)} /><span></span>
                </label>
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
        getEntity('day').then((entity) => {
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
            <div className="daysoff">
                <table className="table--base clean">
                    <thead>
                        <th>Bezeichnung</th>
                        <th>Datum</th>
                        <th>Löschen</th>
                    </thead>
                    <tbody>
                        {this.state.days.map((day, index) => renderDay(day, index, onChange, onDeleteClick))}
                    </tbody>
                    <tfoot>
                        <tr><td colSpan="3">
                            <button className="link button-default" onClick={onNewClick} >
                                <i className="fas fa-plus-square color-positive" aria-hidden="true"></i> Neuer freier Tag
                            </button>
                        </td></tr>
                    </tfoot>
                </table>
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

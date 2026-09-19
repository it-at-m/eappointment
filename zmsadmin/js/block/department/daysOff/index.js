import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'
import * as Inputs from '../../../lib/inputs'
import Datepicker from '../../../lib/inputs/date'

const DUPLICATE_DATE_MESSAGE = 'Ein Datum darf nur einmal als freier Tag gespeichert werden.'

const dateInYear = (year) => {
    const date = new Date()
    date.setFullYear(Number(year) || date.getFullYear())
    return date.getTime() / 1000
}

const emptyDayOff = (date) => ({
    name: '',
    date
})

const toDateKey = (value) => {
    if (value == null || value === '') {
        return ''
    }
    if (typeof value === 'number' || /^\d+(\.\d+)?$/.test(String(value))) {
        const unix = Number(value)
        if (Number.isFinite(unix) && unix > 100000) {
            const parsed = moment.unix(unix)
            return parsed.isValid() ? parsed.format('YYYY-MM-DD') : ''
        }
    }
    const parsed = moment(value, ['YYYY-MM-DD', 'DD.MM.YYYY'], true)
    return parsed.isValid() ? parsed.format('YYYY-MM-DD') : ''
}

const duplicateDateKeys = (days) => {
    const counts = {}
    days.forEach((day) => {
        const key = toDateKey(day.date)
        if (!key) {
            return
        }
        counts[key] = (counts[key] || 0) + 1
    })
    return new Set(Object.keys(counts).filter((key) => counts[key] > 1))
}

const nextFreeDateInYear = (year, days) => {
    const used = new Set(days.map((day) => toDateKey(day.date)).filter(Boolean))
    const date = new Date()
    date.setHours(0, 0, 0, 0)
    date.setFullYear(Number(year) || date.getFullYear())
    const end = new Date(date)
    end.setMonth(11, 31)
    const cursor = new Date(date)
    while (cursor <= end) {
        const key = moment(cursor).format('YYYY-MM-DD')
        if (!used.has(key)) {
            return cursor.getTime() / 1000
        }
        cursor.setDate(cursor.getDate() + 1)
    }
    cursor.setMonth(0, 1)
    while (cursor < date) {
        const key = moment(cursor).format('YYYY-MM-DD')
        if (!used.has(key)) {
            return cursor.getTime() / 1000
        }
        cursor.setDate(cursor.getDate() + 1)
    }
    return date.getTime() / 1000
}

const excludeDatesForIndex = (days, index) => {
    return days
        .map((day, dayIndex) => (dayIndex === index ? null : toDateKey(day.date)))
        .filter(Boolean)
        .map((key) => moment(key, 'YYYY-MM-DD').toDate())
}

const renderDay = (day, index, onChange, onDeleteClick, isDuplicate, excludeDates) => {
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
        <tr className={`daysoff-item${isDuplicate ? ' has-error' : ''}`} key={index}>
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
                    excludeDates={excludeDates}
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
        this.state = { days: [], error: '', duplicateDates: new Set() }
        this.handleSubmit = this.handleSubmit.bind(this)
        this.setContainerRef = this.setContainerRef.bind(this)
    }

    componentWillUnmount() {
        if (this.form) {
            this.form.removeEventListener('submit', this.handleSubmit)
            this.form = null
        }
    }

    setContainerRef(element) {
        if (this.form) {
            this.form.removeEventListener('submit', this.handleSubmit)
            this.form = null
        }
        if (element) {
            this.form = element.closest('form')
            if (this.form) {
                this.form.addEventListener('submit', this.handleSubmit)
            }
        }
    }

    componentDidMount() {
        this.setState((prevState, props) => ({
            days: props.days.length > 0 ? props.days : [emptyDayOff(dateInYear(props.year))]
        }))
    }

    handleSubmit(event) {
        const duplicateDates = duplicateDateKeys(this.state.days)
        if (duplicateDates.size > 0) {
            event.preventDefault()
            this.setState({
                duplicateDates,
                error: DUPLICATE_DATE_MESSAGE
            })
        }
    }

    changeItemField(index, field, value) {
        this.setState((prevState) => {
            const days = prevState.days.map((day, dayIndex) => {
                return index === dayIndex ? Object.assign({}, day, { [field]: value }) : day
            })
            const duplicateDates = duplicateDateKeys(days)
            return {
                days,
                duplicateDates,
                error: duplicateDates.size > 0 ? DUPLICATE_DATE_MESSAGE : ''
            }
        })
    }

    addNewItem() {
        this.setState((prevState, props) => {
            const days = prevState.days.concat([emptyDayOff(nextFreeDateInYear(props.year, prevState.days))])
            const duplicateDates = duplicateDateKeys(days)
            return {
                days,
                duplicateDates,
                error: duplicateDates.size > 0 ? DUPLICATE_DATE_MESSAGE : ''
            }
        })
    }

    deleteItem(deleteIndex) {
        this.setState((prevState) => {
            const days = prevState.days.filter((day, index) => index !== deleteIndex)
            const duplicateDates = duplicateDateKeys(days)
            return {
                days,
                duplicateDates,
                error: duplicateDates.size > 0 ? DUPLICATE_DATE_MESSAGE : ''
            }
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
            <div className="daysoff table-responsive-wrapper" ref={this.setContainerRef}>
                {this.state.error ? (
                    <div className="message message--error" role="alert">
                        <p className="message__body">{this.state.error}</p>
                    </div>
                ) : null}
                <div className="table-action-link">
                    <button type="button" className="link button-default" onClick={onNewClick} >
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
                        {this.state.days.map((day, index) => renderDay(
                            day,
                            index,
                            onChange,
                            onDeleteClick,
                            this.state.duplicateDates.has(toDateKey(day.date)),
                            excludeDatesForIndex(this.state.days, index)
                        ))}
                    </tbody>
                </table>
                <div className="table-action-link">
                    <button type="button" className="link button-default" onClick={onNewClick} >
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

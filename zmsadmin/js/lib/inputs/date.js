/* eslint-disable react/prop-types */
import React from 'react'
import moment from 'moment'
import DatePicker, { registerLocale } from 'react-datepicker'
import de from 'date-fns/locale/de'
registerLocale('de', de)

class Datepicker extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            startDate: moment.unix(props.value).toDate(),
            datePickerIsOpen: false,
        };
        this.handleChange = this.handleChange.bind(this);
        this.openDatePicker = this.openDatePicker.bind(this);
        this.closeDatePicker = this.closeDatePicker.bind(this);
        this.escHandler = this.escHandler.bind(this);
        this.keyDownHandler = this.keyDownHandler.bind(this);
        this.handleIcon = this.handleIcon.bind(this);
    }

    componentDidMount(){
        if (this.props.accessKey)
            this.datepicker.input.accessKey = this.props.accessKey;
        if (this.props.ariaLive)
            this.datepicker.input.ariaLive = "polite";

        document.addEventListener("keydown", this.escHandler, false);
      }

    escHandler(event) {
        if (event.key === "Escape") {
            this.closeDatePicker();
        }
    }

    keyDownHandler(event) {
        if (event.key === 'Enter') {
            event.preventDefault()
            if (this.state.datePickerIsOpen) {
                this.closeDatePicker() 
            } else {
                this.openDatePicker()
            }
        }
    }

    handleIcon(event) {
        event.preventDefault();
        this.openDatePicker()
    }

    handleChange(date) {
        this.setState({
            startDate: date
        });
        this.props.onChange(moment(date, 'X').format('YYYY-MM-DD'))
        this.closeDatePicker();
    }
    
    openDatePicker() {
        this.setState({
            datePickerIsOpen: true,
        });
        this.datepicker.input.focus();
    }

    closeDatePicker() {
        this.setState({
            datePickerIsOpen: false,
        });
    }

    render () {
        const submitValue = this.state.startDate && !isNaN(this.state.startDate.getTime())
            ? moment(this.state.startDate).format('DD.MM.YYYY')
            : ''
        return (
            <div className="add-date-picker" {...this.props.attributes}>
                <input type="hidden" name={this.props.name} value={submitValue} />
                <DatePicker 
                    locale="de" 
                    id={this.props.id}
                    disabled={this.props.disabled}
                    className="form-control form-input" 
                    dateFormat="dd.MM.yyyy"
                    selected={this.state.startDate}
                    onChange={this.handleChange}
                    onInputClick={this.openDatePicker}
                    onKeyDown={this.keyDownHandler}
                    onClickOutside={this.closeDatePicker}
                    strictParsing={true}
                    open={this.state.datePickerIsOpen}
                    ref={(datepicker) => { this.datepicker = datepicker }} 
                    chooseDayAriaLabelPrefix="Datumsauswahl"
                    disabledDayAriaLabelPrefix="Nicht auswählbar"
                    previousMonthAriaLabel="vorheriger Monat"
                    nextMonthAriaLabel="nächster Monat"
                    monthAriaLabelPrefix="Monat"
                />
                <a href="#" className="calendar-placement icon" title="Kalender öffnen" aria-label="Kalender öffnen" onClick={this.handleIcon} onKeyDown={this.keyDownHandler}>
                    <i className="far fa-calendar-alt" aria-hidden="true" />
                </a>
            </div>
            
        )
    }
}

export default Datepicker;
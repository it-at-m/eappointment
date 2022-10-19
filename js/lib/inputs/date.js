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
        this.datepicker.input.accessKey = this.props.accessKey;
        document.addEventListener("keydown", this.escHandler, false);
      }

    escHandler(event) {
        if (event.key === "Escape") {
            this.closeDatePicker();
        }
    }

    keyDownHandler(event) {
        event.preventDefault 
        if (event.key === 'Enter') {
          this.openDatePicker() 
        }
    }

    handleIcon() {
        this.openDatePicker()
        this.datepicker.input.focus();
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
    }

    closeDatePicker() {
        this.setState({
            datePickerIsOpen: false,
        });
    }

    render () {
        return (
            <div className="add-date-picker" {...this.props.attributes}>
                <DatePicker 
                    locale="de" 
                    id={this.props.id}
                    className="form-control form-input" 
                    dateFormat="dd.MM.yyyy"
                    selected={this.state.startDate}
                    onChange={this.handleChange} {...this.props.name } 
                    onInputClick={this.openDatePicker}
                    onKeyDown={this.keyDownHandler}
                    onClickOutside={this.closeDatePicker}
                    strictParsing={true}
                    open={this.state.datePickerIsOpen}
                    ref={(datepicker) => { this.datepicker = datepicker }} 
                />
                <a href="#" className="calendar-placement icon" title="Kalender öffnen" onClick={this.handleIcon}>
                    <i className="far fa-calendar-alt" alt="Kalender öffnen" />
                </a>
            </div>
            
        )
    }
}

export default Datepicker;
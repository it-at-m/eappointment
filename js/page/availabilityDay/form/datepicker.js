/* eslint-disable react/prop-types */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'
import setHours from "date-fns/setHours"
import setMinutes from "date-fns/setMinutes";
import DatePicker, { registerLocale } from 'react-datepicker'
import de from 'date-fns/locale/de';
//import {formatTimestampDate} from "../helpers"
registerLocale('de', de)

class AvailabilityDatePicker extends Component 
{
    constructor(props) {
        super(props);
        if (props.name == 'startDate') {
            var time = moment(props.attributes.availability.startTime, 'hh:mm');
            var date = moment.unix(props.attributes.availability.startDate)
                .add(time.hours(), 'h').add(time.minutes(), 'm')
                .toDate()
        } else {
            var time = moment(props.attributes.availability.endTime, 'hh:mm');
            var date = moment.unix(props.attributes.availability.endDate)
                .add(time.hours(), 'h').add(time.minutes(), 'm')
                .toDate()
        }
        this.state = {
            availability: this.props.attributes.availability,
            conflictList: [],
            availabilitylist: [props.attributes.availabilitylist],
            selectedDate: date,
            excludeDateList: [moment(props.attributes.availability.startDate, 'X').toDate()]
        };
    }

    componentDidMount() {
        this.getExcludeDates();
    }

    componentDidUpdate(prevProps) {
        if (prevProps.attributes.availability.type != this.props.attributes.availability.type) {
            this.getExcludeDates();
        }
    }

    getExcludeDates() {
        let dates = []
        this.props.attributes.availabilitylist.map(availability => {
            if (
                availability.id !== this.props.attributes.availability.id &&
                availability.type == this.props.attributes.availability.type
            ) {
                let startDate = moment(availability.startDate, 'X').toDate()
                let endDate = moment(availability.endDate, 'X').toDate()
                const currentDate = new Date(startDate)
                while (currentDate < endDate) {
                    dates = [...dates, new Date(currentDate)]
                    currentDate.setDate(currentDate.getDate() + 1)
                }
                dates = [...dates, endDate]
            }
        });
        this.setState({excludeDateList: dates});
    }

    render() {
        const {onChange} = this.props;
        const onPickDate = date => {
            this.setState({
                selectedDate: date,
            });
            if (this.props.name == "startDate") 
                onChange("startDate", date);
            if (this.props.name == "endDate")
                onChange("endDate", date);
        }

        const dayClassName = (date) => {
            let className = undefined;
            this.state.excludeDateList.map(excludedDate => {
                if (
                    excludedDate.getDate() === date.getDate() &&
                    excludedDate.getMonth() === date.getMonth() &&
                    excludedDate.getFullYear() === date.getFullYear()
                ) {
                    className = `day__${this.props.attributes.availability.type}`
                }
            })
            return className;
        }

        const isWeekday = date => {
            const day = date.getDay();
            return day !== 0 && day !== 6;
        };

        return (
            <div className="add-date-picker" id={this.props.attributes.id}>
                <DatePicker 
                    locale="de" 
                    className="form-control form-input" 
                    
                    dateFormat="dd.MM.yyyy HH:mm" 
                    selected={this.state.selectedDate} 
                    onChange={onPickDate}
                    //minDate={this.state.minDate}
                    //maxDate={endDate}
                    filterDate={isWeekday}
                    excludeDates={this.state.excludeDateList}

                    showTimeSelect
                    timeFormat="HH:mm"
                    timeIntervals={5}
                    timeCaption="Uhrzeit"
                    minTime={setHours(setMinutes(new Date(), 0), 7)}
                    maxTime={setHours(setMinutes(new Date(), 0), 20)}
                   
                    dayClassName={dayClassName}
                    disabledKeyboardNavigation
                    showDisabledMonthNavigation
                />
                {/*<div className="react-datepicker__day react-datepicker__day--disabled day__appointment">x</div>Vorhandene Terminkunden-Öffnungszeit<br />
                <div className="react-datepicker__day react-datepicker__day--disabled day__openinghours">x</div>Vorhandene Spontankunden-Öffnungszeit*/}
            </div>
        )
    }
}

AvailabilityDatePicker.propTypes = {
    onChange: PropTypes.func,
    attributes: PropTypes.object
}

export default AvailabilityDatePicker

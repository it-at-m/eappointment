/* eslint-disable react/prop-types */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'
import setHours from "date-fns/setHours"
import setMinutes from "date-fns/setMinutes";
import DatePicker, { registerLocale } from 'react-datepicker'
import {weekDayList, repeat} from '../helpers'
import de from 'date-fns/locale/de';
//import {formatTimestampDate} from "../helpers"
registerLocale('de', de)

class AvailabilityDatePicker extends Component 
{
    constructor(props) {
        super(props);
        this.state = {
            focused: this.props.attributes.id,
            availability: this.props.attributes.availability,
            availabilityList: this.props.attributes.availabilitylist,
            minDate: moment.unix(this.props.attributes.availability.startDate).toDate()
        }
    }

    componentDidUpdate(prevProps) {
        if (
            this.state.availability && 
            prevProps.attributes.availability !== this.props.attributes.availability
        ) {
            this.updateState()
        }
        
    }

    updateState(name, date) {
        let startTime = moment(this.props.attributes.availability.startTime, 'HH:mm');
        let startDate = moment.unix(this.props.attributes.availability.startDate)
            .set({"h": startTime.hours(), "m": startTime.minutes()})
            .toDate()

        let endTime = moment(this.props.attributes.availability.endTime, 'HH:mm');
        let endDate = moment.unix(this.props.attributes.availability.endDate)
            .set({"h": endTime.hours(), "m": endTime.minutes()})
            .toDate()

        let selectedDate = ("startDate" == this.props.name) ? startDate : endDate

        if (name && date) {
            startDate = ("startDate" == name) ? date.startOf('day') : startDate;
            endDate = ("endDate" == name) ? date.startOf('day') : endDate;
            selectedDate = date
        }
        this.setState({
            availability: this.props.attributes.availability,
            availabilityList: this.props.attributes.availabilitylist,
            selectedDate: selectedDate,
            startDate: startDate,
            endDate: endDate,
            minTime: setHours(setMinutes(new Date(), 0), 7),
            maxTime: setHours(setMinutes(new Date(), 0), 20)
        }, () => {
            this.setExcludeTimesForDay()
        })
    }

    setClassNameForSelectedWeekDay(className, date) {
        if (this.isWeekDaySelected(date) &&
            this.isDateInAvailabilityRange(date)
        ) {
            className = `${className} day__selected__weekday__${this.state.availability.type}`; 
        }
        return className
    }

    isDateInAvailabilityRange(date) {
        return (
            date <= moment.unix(this.state.availability.endDate).toDate() && 
            date >= moment.unix(this.state.availability.startDate).toDate()
        )
    }

    isDateEqual(date1, date2) {
        return (
            date1.getDate() === date2.getDate() &&
            date1.getMonth() === date2.getMonth() &&
            date1.getFullYear() === date2.getFullYear()
        )
    }

    isWeekDaySelected(date, availability)
    {
        const selectedAvailability = availability ? availability : this.state.availability
        let isSelected = false;
        for (const [key, value] of Object.entries(selectedAvailability.weekday)) {
            weekDayList.map((weekday, index) => {
                if ((index+1) == date.getDay() && weekday.value == key && value > 0) {
                    isSelected = true; 
                }   
            }) 
        } 
        return isSelected;
    }

    setExcludeTimesForDay() {
        var times = []
        this.state.availabilityList.map(availability => {
            if (availability.id !== this.state.availability.id &&
                availability.type == this.state.availability.type &&
                this.isWeekDaySelected(this.state.selectedDate, availability)
            ) {
                const startTime = moment(availability.startTime, 'hh:mm');
                const startOnDay = moment(this.state.selectedDate)
                    .set({"h": startTime.hours(), "m": startTime.minutes()})
                    .toDate()
                
                const endTime = moment(availability.endTime, 'hh:mm');
                const endOnDay = moment(this.state.selectedDate)
                    .set({"h": endTime.hours(), "m": endTime.minutes()})
                    .toDate()
               
                var currentTime = new Date(startOnDay)
                while (currentTime <= endOnDay) {
                    times = [...times, new Date(currentTime)]
                    currentTime = moment(currentTime)
                        .add(this.state.availability.slotTimeInMinutes, "m")
                        .toDate()
                }
                times = [...times, endOnDay]
            }
        });
        this.setState({excludeTimeList: times});
    }

    handleChange(name, date) {
        if ('startDate' == name) {
            if (this.state.availability.startDate != moment(date).startOf('day').unix()) {
                this.props.onChange("startDate", moment(date).unix());
            }
            if (this.state.availability.startTime != moment(date).format('HH:mm')) {
                this.props.onChange("startTime", moment(date).format('HH:mm'));
            }
        }
        if ('endDate' == name) {
            if (this.state.availability.endDate != moment(date).startOf('day').unix()) {
                this.props.onChange("endDate", moment(date).unix());
            }
            if (this.state.availability.endTime != moment(date).format('HH:mm')) {
                this.props.onChange("endTime", moment(date).format('HH:mm'));
            }
        } 
       
    }

    render() {
        const dayClassName = (date) => {
            let className = "";
            className = this.setClassNameForSelectedWeekDay(className, date);
            return className;
        }

        /*
        const isWeekday = date => {
            const day = date.getDay();
            return day !== 0 && day !== 6;
        };
        */

        return (
            <div className="add-date-picker">
                <DatePicker 
                    locale="de" 
                    className="form-control form-input" 
                    id={this.props.attributes.id}
                    name={this.props.name}
                    dateFormat="dd.MM.yyyy HH:mm" 
                    selected={this.state.selectedDate}
                    onChange={date => {this.handleChange(this.props.name, date)}}
                    minDate={this.state.minDate}
                    maxDate={repeat(this.state.availability.repeat) == 0 ? this.state.selectedDate : null}
                    //filterDate={isWeekday}
                    //excludeDates={this.state.excludeDateList}
                
                    showTimeSelect
                    timeFormat="HH:mm"
                    timeIntervals={this.state.availability.slotTimeInMinutes || 10}
                    timeCaption="Uhrzeit"
                    minTime={this.state.minTime}
                    maxTime={this.state.maxTime}
                    excludeTimes={this.state.excludeTimeList}
                    
                    dayClassName={dayClassName}
                    //disabledKeyboardNavigation
                    showDisabledMonthNavigation
                    disabled={this.props.attributes.disabled}
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

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
        const startTime = moment(props.attributes.availability.startTime, 'hh:mm');
        const startDate = moment.unix(props.attributes.availability.startDate)
            .set({"h": startTime.hours(), "m": startTime.minutes()})
            .toDate()

        const endTime = moment(props.attributes.availability.endTime, 'hh:mm');
        const endDate = moment.unix(props.attributes.availability.endDate)
            .set({"h": endTime.hours(), "m": endTime.minutes()})
            .toDate()
          
        this.state = {
            availability: props.attributes.availability,
            selectedDate: ("startDate" == props.name) ? startDate : endDate,
            startDate: startDate,
            endDate: endDate,
            excludeTimeList: [],
            minTime: setHours(setMinutes(new Date(), 0), 7),
            maxTime: setHours(setMinutes(new Date(), 0), 20)
        };
    }

    componentDidUpdate(prevProps) {
        if (prevProps.attributes.availability !== this.props.attributes.availability) {
            this.setState({
                availability: this.props.attributes.availability
            });
        }
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
            date <= moment.unix(this.state.availability.endDate).startOf('day').toDate() && 
            date >= moment.unix(this.state.availability.startDate).startOf('day').toDate()
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

    setExcludeTimesForDay(date) {
        var times = []
        this.props.attributes.availabilitylist.map(availability => {
            if (availability.id !== this.props.attributes.availability.id &&
                availability.type == this.props.attributes.availability.type &&
                this.isWeekDaySelected(date, availability)
            ) {
                const startTime = moment(availability.startTime, 'hh:mm');
                const startOnDay = moment(date).set({"h": startTime.hours(), "m": startTime.minutes()})
                    .toDate()
                
                const endTime = moment(availability.endTime, 'hh:mm');
                const endOnDay = moment(date).set({"h": endTime.hours(), "m": endTime.minutes()})
                    .toDate()
               
                var currentTime = new Date(startOnDay)
                while (currentTime < endOnDay) {
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

    render() {
        const {onChange} = this.props;
        const handleChange = date => {
            this.setState({
                selectedDate: date,
            });
            this.setExcludeTimesForDay(date)
            if (this.props.name == "startDate") {
                onChange("startTime", moment(date).format('HH:mm'));
            }
            if (this.props.name == "endDate") {
                onChange("endTime", moment(date).format('HH:mm'));
            }
            onChange(this.props.name, moment(date).unix());
        }

        const handleCalendarOpen = () => {
            this.setExcludeTimesForDay(this.state.selectedDate);
        }

        const dayClassName = (date) => {
            let className = "";
            className = this.setClassNameForSelectedWeekDay(className, date);
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
                    onChange={handleChange}
                    minDate={this.state.startDate}
                    maxDate={repeat(this.state.availability.repeat) == 0 ? this.state.selectedDate : null}
                    filterDate={isWeekday}
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
                    onCalendarOpen={handleCalendarOpen}
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

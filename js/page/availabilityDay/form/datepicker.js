/* eslint-disable react/prop-types */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'
import setHours from "date-fns/setHours"
import setMinutes from "date-fns/setMinutes";
import DatePicker, { registerLocale } from 'react-datepicker'
import {weekDayList} from '../helpers'
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
            conflictList: [],
            selectedDate: ("startDate" == props.name) ? startDate : endDate,
            startDate: startDate,
            endDate: endDate,
            excludeDateList: [moment(props.attributes.availability.startDate, 'X').toDate()],
            excludeTimeList: [],
            minTime: setHours(setMinutes(new Date(), 0), 7),
            maxTime: setHours(setMinutes(new Date(), 0), 20)
        };
    }

    componentDidMount() {
        //this.getExcludeDates();
        this.setExcludeTimesForDay(this.state.selectedDate);
    }

    componentDidUpdate(prevProps) {
        if (prevProps.attributes.availability != this.props.attributes.availability) {
            //this.getExcludeDates();
            this.setState({availability: this.props.attributes.availability})
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

    hasFreeSlotsOnDay(date) {

    }

    setClassNameForSelectedWeekDay(className, date) {
        const day = date.getDay();
        this.props.attributes.availabilitylist.map(availability => {
            if (availability.id !== this.props.attributes.availability.id &&
                availability.type == this.props.attributes.availability.type &&
                this.isWeekDaySelected(availability, date)
            ) {
                className = `${className} day__selected__weekday__${availability.type}`; 
            }
        })
        return className
    }

    setClassNameForExcludedDay(className, date) {
        this.state.excludeDateList.map(excludedDate => {
            if (
                excludedDate.getDate() === date.getDate() &&
                excludedDate.getMonth() === date.getMonth() &&
                excludedDate.getFullYear() === date.getFullYear()
            ) {
                className = `day__${this.state.availability.type}`
            }
        })
        return className;
    }

    isWeekDaySelected(availability, date)
    {
        let isSelected = false;
        for (const [key, value] of Object.entries(availability.weekday)) {
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
                this.isWeekDaySelected(availability, date)
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
        return times;
    }

    render() {
        const {onChange} = this.props;
        const onPickDate = date => {
            this.setState({
                selectedDate: date,
            });
            this.setExcludeTimesForDay(date);
            onChange(this.props.name, moment(date).unix());
            if (this.props.name == "startDate") 
                onChange("startTime", moment(date).format('hh:mm'));
            if (this.props.name == "endDate")
                onChange("endTime", moment(date).format('hh:mm'));
        }

        const dayClassName = (date) => {
            let className = "";
            let hasFreeSlots = this.hasFreeSlotsOnDay(date);
            className = this.setClassNameForExcludedDay(className, date);
            className = this.setClassNameForSelectedWeekDay(className, date);
            return className;
        }

        const isWeekday = date => {
            const day = date.getDay();
            return day !== 0 && day !== 6;
        };

        const getDayInfo = date => {
            console.log(date)
        }
        

        return (
            <div className="add-date-picker" id={this.props.attributes.id}>
                <DatePicker 
                    locale="de" 
                    className="form-control form-input" 
                    
                    dateFormat="dd.MM.yyyy HH:mm" 
                    selected={this.state.selectedDate} 
                    onChange={onPickDate}
                    minDate={moment(this.state.availability.startDate, "X")}
                    //maxDate={this.state.selectedDate}
                    filterDate={isWeekday}
                    //excludeDates={this.state.excludeDateList}
                
                    showTimeSelect
                    timeFormat="HH:mm"
                    timeIntervals={this.state.availability.slotTimeInMinutes || 10}
                    timeCaption="Uhrzeit"
                    minTime={this.state.minTime}
                    maxTime={this.state.maxTime}
                    //onDayMouseEnter={getDayInfo}
                    excludeTimes={this.state.excludeTimeList}
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

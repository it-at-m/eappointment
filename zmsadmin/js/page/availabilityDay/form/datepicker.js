/* eslint-disable react/prop-types */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'
import setHours from "date-fns/setHours"
import setMinutes from "date-fns/setMinutes";
import DatePicker, { registerLocale } from 'react-datepicker'
import { weekDayList, repeat } from '../helpers'
import * as Inputs from '../../../lib/inputs'
const { Label, Description } = Inputs
import de from 'date-fns/locale/de';
//import {formatTimestampDate} from "../helpers"
registerLocale('de', de)

class AvailabilityDatePicker extends Component {
    constructor(props) {
        super(props);
        this.state = {
            kind: this.props.attributes.kind,
            availability: this.props.attributes.availability,
            availabilityList: this.props.attributes.availabilitylist,
            minDate: moment.unix(this.props.attributes.availability.startDate).toDate(),
            minTime: setHours(setMinutes(new Date(), 1), 0),
            maxTime: setHours(setMinutes(new Date(), 59), 22),
            datePickerIsOpen: false,
            timPickerIsOpen: false
        }
        this.escHandler = this.escHandler.bind(this);
        //datepicker
        this.openDatePicker = this.openDatePicker.bind(this);
        this.closeDatePicker = this.closeDatePicker.bind(this);
        this.dpKeyDownHandler = this.dpKeyDownHandler.bind(this);
        this.handleCalendarIcon = this.handleCalendarIcon.bind(this);
        //timepicker
        this.openTimePicker = this.openTimePicker.bind(this);
        this.closeTimePicker = this.closeTimePicker.bind(this);
        this.tpKeyDownHandler = this.tpKeyDownHandler.bind(this);
        this.handleClockIcon = this.handleClockIcon.bind(this);



    }

    componentDidMount() {
        document.addEventListener("keydown", this.escHandler, false);
        this.datepicker.input.ariaLive = "polite";
        this.timepicker.input.ariaLive = "polite";

        this.updateState()
    }

    componentDidUpdate(prevProps) {
        if (this.props.attributes.availability !== prevProps.attributes.availability) {
            this.updateState()
        }
    }

    updateState(name, date) {
        let startTime = moment(this.props.attributes.availability.startTime, 'HH:mm');
        let startDate = moment.unix(this.props.attributes.availability.startDate)
            .set({ "h": startTime.hours(), "m": startTime.minutes() })
            .toDate()
        let endTime = moment(this.props.attributes.availability.endTime, 'HH:mm');
        let endDate = moment.unix(this.props.attributes.availability.endDate)
            .set({ "h": endTime.hours(), "m": endTime.minutes() })
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
            selectedDate: selectedDate
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

    isWeekDaySelected(date, availability) {
        const selectedAvailability = availability ? availability : this.state.availability
        let isSelected = false;
        for (const [key, value] of Object.entries(selectedAvailability.weekday)) {
            weekDayList.map((weekday, index) => {
                if ((index + 1) == date.getDay() && weekday.value == key && value > 0) {
                    isSelected = true;
                }
            })
        }
        return isSelected;
    }

    setExcludeTimesForDay() {
        if (this.state.kind === 'exclusion') {
            return;
        }
        var times = [];

        // Add maintenance window times (22:00-01:00)
        const selectedDate = moment(this.state.selectedDate);
        for (let minute = 1; minute < 60; minute++) {
            times.push(selectedDate.clone().hour(22).minute(minute).toDate());
        }
        for (let minute = 0; minute < 59; minute++) {
            times.push(selectedDate.clone().hour(0).minute(minute).toDate());
        }

        // Filter and sort availabilities
        const availabilities = [...this.state.availabilityList]
            .filter(availability =>
                availability.id !== this.state.availability.id &&
                availability.type == this.state.availability.type &&
                this.isWeekDaySelected(this.state.selectedDate, availability)
            )
            .sort((a, b) => {
                const timeA = moment(a.startTime, 'HH:mm');
                const timeB = moment(b.startTime, 'HH:mm');
                return timeA.diff(timeB);
            });

        // Add regular excluded times
        availabilities.forEach(availability => {
            const startTime = moment(availability.startTime, 'hh:mm')
                .add(this.state.availability.slotTimeInMinutes, "m");
            const startOnDay = moment(this.state.selectedDate)
                .set({ "h": startTime.hours(), "m": startTime.minutes() })
                .toDate();

            const endTime = moment(availability.endTime, 'hh:mm')
                .subtract(this.state.availability.slotTimeInMinutes, "m");
            const endOnDay = moment(this.state.selectedDate)
                .set({ "h": endTime.hours(), "m": endTime.minutes() })
                .toDate();

            var currentTime = new Date(startOnDay);
            while (currentTime < endOnDay) {
                times.push(new Date(currentTime));
                currentTime = moment(currentTime)
                    .add(this.state.availability.slotTimeInMinutes, "m")
                    .toDate();
            }
            times.push(endOnDay);
        });

        // Add boundary timestamps between adjacent availabilities
        for (let i = 0; i < availabilities.length - 1; i++) {
            const current = availabilities[i];
            const next = availabilities[i + 1];

            const currentEnd = moment(current.endTime, 'HH:mm');
            const nextStart = moment(next.startTime, 'HH:mm');

            // If they're adjacent (end time of one equals start time of next)
            if (currentEnd.format('HH:mm') === nextStart.format('HH:mm')) {
                // Add the boundary timestamp to excluded times
                const boundaryTime = moment(this.state.selectedDate)
                    .set({ "h": currentEnd.hours(), "m": currentEnd.minutes() })
                    .toDate();
                times.push(boundaryTime);
            }
        }

        this.setState({ excludeTimeList: times });
    }

    handleChange(name, date) {
        if (!date) {
            this.closeDatePicker();
            return;
        }
        if ('startDate' == name) {
            if (this.state.availability.startDate != moment(date).startOf('day').unix()) {
                this.props.onChange("startDate", moment(date).unix());
            }
        }
        if ('endDate' == name) {
            if (this.state.availability.endDate != moment(date).startOf('day').unix()) {
                this.props.onChange("endDate", moment(date).unix());
            }
        }
        this.closeDatePicker();
    }

    handleTimeChange(name, date) {
        if ('startDate' == name) {
            if (this.state.availability.startTime != moment(date).format('HH:mm')) {
                this.props.onChange("startTime", moment(date).format('HH:mm'));
            }
        }
        if ('endDate' == name) {
            if (this.state.availability.endTime != moment(date).format('HH:mm')) {
                this.props.onChange("endTime", moment(date).format('HH:mm'));
            }
        }
        this.closeTimePicker();
    }

    escHandler(event) {
        if (event.key === "Escape") {
            this.closeDatePicker();
            this.closeTimePicker();
        }
    }

    dpKeyDownHandler(event) {
        if (event.key === 'Enter') {
            event.preventDefault()
            this.openDatePicker()
        }
    }

    tpKeyDownHandler(event) {
        if (event.key === 'Enter') {
            event.preventDefault()
            this.openTimePicker()
        }
    }

    handleCalendarIcon(event) {
        event.preventDefault();
        this.openDatePicker()
        this.datepicker.input.focus();
    }

    handleClockIcon(event) {
        event.preventDefault();
        this.openTimePicker()
        this.timepicker.input.focus();
    }

    openDatePicker() {
        if (!this.props.attributes.disabled) {
            this.setState({
                datePickerIsOpen: true,
            });
        }
    }

    openTimePicker() {
        if (!this.props.attributes.disabled) {
            this.setState({
                timePickerIsOpen: true,
            });
        }

    }

    closeDatePicker() {
        this.setState({
            datePickerIsOpen: false,
        });
    }

    closeTimePicker() {
        this.setState({
            timePickerIsOpen: false,
        });
    }

    render() {
        const dayClassName = (date) => {
            let className = "";
            className = this.setClassNameForSelectedWeekDay(className, date);
            return className;
        }

        const filterPassedTime = (time) => {
            // For future dates, only check maintenance window
            if (!moment(this.state.selectedDate).isSame(moment.unix(this.props.attributes.today), 'day')) {
                const hour = time.getHours();
                const minutes = time.getMinutes();
                // Block maintenance window (22:00-01:00)
                return !(hour >= 22 || hour < 1 || (hour === 22 && minutes > 0));
            }
            
            const currentTime = moment();
            const timeToCheck = moment(time);
            const minutesDiff = timeToCheck.diff(currentTime, 'minutes');

            return minutesDiff >= 0;
        };

        /*
        const isWeekday = date => {
            const day = date.getDay();
            return day !== 0 && day !== 6;
        };
        */

        return (
            <div className="grid">
                <div className="grid__item one-half">
                    <div className="form-group">
                        <Label
                            attributes={{ "htmlFor": this.props.attributes.id, "className": "light" }}
                            value={"startDate" == this.props.name ? "Datum von" : "Datum bis"}>
                        </Label>
                        <div className="controls add-date-picker">
                            <DatePicker
                                todayButton="Heute"
                                locale="de"
                                className="form-control form-input"
                                id={this.props.attributes.id}
                                ariaDescribedBy={"help_" + this.props.attributes.id}
                                name={this.props.name}
                                dateFormat="dd.MM.yyyy"
                                selected={this.state.selectedDate}
                                onChange={date => { this.handleChange(this.props.name, date) }}
                                minDate={this.state.minDate}
                                //maxDate={repeat(this.state.availability.repeat) == 0 ? this.state.selectedDate : null}
                                //filterDate={isWeekday}
                                //excludeDates={this.state.excludeDateList}                   
                                dayClassName={dayClassName}
                                disabled={this.props.attributes.disabled}
                                onInputClick={this.openDatePicker}
                                onKeyDown={this.dpKeyDownHandler}
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
                            <a aria-describedby={"help_" + this.props.attributes.id} href="#" aria-label={"startDate" == this.props.name ? "Kalender Datum von öffnen" : "Kalender Datum bis öffnen"} className="calendar-placement icon" title={"startDate" == this.props.name ? "Kalender Datum von öffnen" : "Kalender Datum bis öffnen"} onClick={this.handleCalendarIcon} onKeyDown={this.dpKeyDownHandler}>
                                <i className="far fa-calendar-alt" aria-hidden="true" />
                            </a>
                        </div>
                        <Description attributes={{ "id": "help_" + this.props.attributes.id }} value="Eingabe des Datums im Format TT.MM.YYYY"></Description>
                    </div>
                </div>
                <div className="grid__item one-half">
                    <div className="form-group">
                        <Label
                            attributes={{ "htmlFor": this.props.attributes.id + "_time", "className": "light" }}
                            value={"startDate" == this.props.name ? "Uhrzeit von" : "Uhrzeit bis"}>
                        </Label>
                        <div className="controls add-date-picker">
                            <DatePicker
                                name={this.props.name + "_time"}
                                locale="de"
                                className="form-control form-input"
                                ariaDescribedBy={"help_" + this.props.attributes.id + "_time"}
                                id={this.props.attributes.id + "_time"}
                                selected={this.state.selectedDate}
                                onChange={date => { this.handleTimeChange(this.props.name, date) }}
                                showTimeSelect
                                showTimeSelectOnly
                                dateFormat="HH:mm"
                                timeFormat="HH:mm"
                                timeIntervals={this.state.availability.slotTimeInMinutes <= 30 ?
                                    this.state.availability.slotTimeInMinutes : 30}
                                timeCaption="Uhrzeit"
                                minTime={this.state.minTime}
                                maxTime={this.state.maxTime}
                                excludeTimes={this.state.excludeTimeList}
                                filterTime={filterPassedTime}
                                disabled={this.props.attributes.disabled}
                                onInputClick={this.openTimePicker}
                                onKeyDown={this.tpKeyDownHandler}
                                onClickOutside={this.closeTimePicker}
                                strictParsing={true}
                                open={this.state.timePickerIsOpen}
                                ref={(timepicker) => { this.timepicker = timepicker }}
                            />
                            <a href="#" aria-describedby={"help_" + this.props.attributes.id + "_time"} aria-label="Uhrzeitauswahl öffnen" className="calendar-placement icon" title={"startDate" == this.props.name ? "Uhrzeit von wählen" : "Uhrzeit bis wählen"} onClick={this.handleClockIcon} onKeyDown={this.tpKeyDownHandler}>
                                <i className="far fa-clock" aria-hidden="true" />
                            </a>
                        </div>
                        <Description attributes={{ "id": "help_" + this.props.attributes.id + "_time" }} value="Eingabe der Uhrzeit im Format HH:MM"></Description>
                    </div>
                </div>
            </div>
        )
    }
}

AvailabilityDatePicker.propTypes = {
    onChange: PropTypes.func,
    attributes: PropTypes.object
}

export default AvailabilityDatePicker
/* eslint-disable react/prop-types */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'
import DatePicker, { registerLocale } from 'react-datepicker'
import de from 'date-fns/locale/de';
//import {formatTimestampDate} from "../helpers"
registerLocale('de', de)

class AvailabilityDatePicker extends Component 
{
    constructor(props) {
        super(props);
        this.state = {
            availability: this.props.attributes.availability,
            conflictList: [],
            availabilitylist: [props.attributes.availabilitylist],
            startDate: moment(props.attributes.availability.startDate, 'X').toDate(),
            endDate: moment(props.attributes.availability.endDate, 'X').toDate(),
            minDate: moment(props.attributes.availability.startDate, 'X').toDate(),
            excludeDateList: [moment(props.attributes.availability.startDate, 'X').toDate()]
        };
    }

    componentDidMount() {
        this.getExcludeDates()
    }

    componentDidUpdate(prevProps) {
        if (prevProps.attributes.availability.type != this.props.attributes.availability.type) {
            this.getExcludeDates()
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
        const onSelectRange = dates => {
            const [start, end] = dates;
            this.setState({
                startDate: start,
                endDate: end,
                selectedDate: start
            });
            if (start) 
                onChange("startDate", start);
            if (end)
                onChange("endDate", end);
        }

        const dayClassName = (date) => {
            let className = undefined;
            this.state.excludeDateList.map(excludedDate => {
                if (excludedDate.getDate() === date.getDate()) {
                    className = `day__${this.props.attributes.availability.type}`
                };
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
                    dateFormat="dd.MM.yyyy" 
                    selected={this.state.startDate} 
                    startDate={this.state.startDate} 
                    endDate={this.state.endDate} 
                    onChange={onSelectRange}
                    minDate={this.state.minDate}
                    //maxDate={endDate}
                    showDisabledMonthNavigation
                    filterDate={isWeekday}
                    excludeDates={this.state.excludeDateList}
                    placeholderText="Select a weekday"
                    selectsRange
                    inline
                    monthsShown={3}
                    dayClassName={dayClassName}
                    //showWeekNumbers
                    disabledKeyboardNavigation
                    disabled
                />
            </div>
        )
    }
}

AvailabilityDatePicker.propTypes = {
    onChange: PropTypes.func,
    attributes: PropTypes.object
}

export default AvailabilityDatePicker

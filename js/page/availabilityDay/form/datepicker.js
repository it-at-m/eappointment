/* eslint-disable react/prop-types */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'
import DatePicker, { registerLocale } from 'react-datepicker'
import de from 'date-fns/locale/de';
import {formatTimestampDate} from "../helpers"
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
        };
    }

    componentDidMount() {
        //this.fetchConflicts(formatTimestampDate(this.props.attributes.today));
    }

    componentDidUpdate(prevProps) {
        if (this.props.attributes.availability.startDate !== prevProps.attributes.availability.startDate) {
            this.setState({
                startDate: this.props.attributes.availability.startDate
            })
        }
    }

    fetchConflicts(date) {
        const url = `${this.props.attributes.includeurl}/scope/${this.state.availability.scope.id}/availability/day/${date}/conflicts/`
        fetch(url)
            .then(res => res.json())
            .then(
                (result) => {
                    this.setState({
                        conflictList: result.conflicts
                    });
                },
                (error) => {
                    console.log(error)
                }
            )
    }

    render() {
        const {onChange} = this.props;

        const onSelectRange = dates => {
            const [start, end] = dates;
            this.setState({
                startDate: start,
                endDate: end
            });
            if (start) 
                onChange("startDate", start);
            if (end)
                onChange("endDate", end);
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
                    selected={moment(this.props.attributes.selectedday, "X").toDate()} 
                    startDate={this.state.startDate} 
                    endDate={this.state.endDate} 
                    onChange={onSelectRange}
                    minDate={moment(this.props.attributes.today, "X").toDate()}
                    //maxDate={endDate}
                    showDisabledMonthNavigation
                    filterDate={isWeekday}
                    //excludeDates={[new Date(), subDays(new Date(), 1)]}
                    placeholderText="Select a weekday"
                    selectsRange
                    inline
                    monthsShown={3}
                    //showWeekNumbers
                />
            </div>
        )
    }
}

AvailabilityDatePicker.propTypes = {
    name: PropTypes.string,
    value: PropTypes.number,
    onChange: PropTypes.func,
    attributes: PropTypes.object
}

export default AvailabilityDatePicker

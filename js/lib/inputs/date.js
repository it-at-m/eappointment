/* eslint-disable react/prop-types */
import React, { useRef } from 'react'
import moment from 'moment'
import DatePicker, { registerLocale } from 'react-datepicker'
import de from 'date-fns/locale/de'
registerLocale('de', de)

export const Date = ({name, value, onChange, attributes = {}}) => { 

    const datepickerRef = useRef(null);

    const onPick = (date) => {
        onChange(name, moment(date, 'X').unix())
    }

    const onFocus = () => {
        const datepickerElement = datepickerRef.current;
        console.log(datepickerElement);
        datepickerElement.setState({open:false});
      };

    const handleClickCalendarIcon = () => {
        const datepickerElement = datepickerRef.current;
        datepickerElement.setFocus(true);
    }

    return (
        <div className="add-date-picker" {...attributes}>
            <DatePicker 
                locale="de" 
                className="form-control form-input" 
                dateFormat="dd.MM.yyyy" 
                selected={moment.unix(value).toDate()} 
                onChange={onPick} {...{ name }} 
                onFocus={onFocus}
                strictParsing={true}
                ref={datepickerRef}
            />
            <div className="calender-placment" onClick={() => handleClickCalendarIcon()}>
                <i className="far fa-calendar-alt" />
            </div>
        </div>
        
    )
}


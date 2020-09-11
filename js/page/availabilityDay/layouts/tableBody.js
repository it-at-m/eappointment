import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales';
import {weekDayList, availabilitySeries, availabilityTypes, repeat} from '../helpers'
moment.locale('de')

const TableBodyLayout = (props) => {
    const { onDelete, onSelect, availabilities } = props;
    return (
        <div className="table-responsive-wrapper"> 
            <table className="table--base">
                <thead>
                    <tr>
                        <th></th>
                        <th>Wochentage</th>
                        <th>Serie</th>
                        <th>Von</th>
                        <th>Bis</th>
                        <th>Uhrzeit</th>
                        <th>Typ</th>
                        <th>Zeitschlitz</th>
                        <th>Arbeitsplätze</th>
                        <th>Buchung</th>
                        <th>Anmerkung</th>
                    </tr>
                </thead>
                <tbody>
                {renderTable(onDelete, onSelect, availabilities)}
                </tbody>
            </table>
        </div>
    )
}

const renderTable = (onDelete, onSelect, availabilities) => {
    if (availabilities.length > 0) {
        return availabilities.map((availability, key) => {

            const startDate = moment(availability.startDate, 'X').format('DD.MM.YYYY');
            const endDate = moment(availability.endDate, 'X').format('DD.MM.YYYY');
            const startTime = moment(availability.startTime, 'h:mm:ss').format('HH:mm');
            const endTime = moment(availability.endTime, 'h:mm:ss').format('HH:mm');

            const titleEdit = `Bearbeiten von ${availability.id} (${startDate} - ${endDate})`
            const titleDelete = `Löschen von ${availability.id} (${startDate} - ${endDate})`

            const onClickEdit = ev => {
                ev.preventDefault()
                onSelect(availability)
            }

            const onClickDelete = ev => {
                ev.preventDefault()
                onDelete(availability)
            }

            const availabilityWeekDayList = Object.keys(availability.weekday).filter(key => parseInt(availability.weekday[key], 10) > 0)
            
            const availabilityWeekDay = weekDayList.filter(element => availabilityWeekDayList.includes(element.value)).map(item => item.label).join(', ')

            const availabilityRepeat = availabilitySeries.find(element => element.value == repeat(availability.repeat)).name

            const availabilityType = availabilityTypes.find(element => element.value == availability.type)
            
            return (
                <tr key={key}>
                    <td className="center" style={{"whiteSpace": "nowrap"}}>
                        <span style={{ marginRight: "5px" }}>
                            <a href="#" className="icon" title={titleEdit} onClick={onClickEdit}>
                                <i className="fas fa-pencil-alt" aria-hidden="true"></i>
                            </a>
                        </span>
                        <span>
                            <a href="#" className="icon" title={titleDelete} onClick={onClickDelete}>
                                <i className="far fa-trash-alt" aria-hidden="true"></i>
                            </a>
                        </span>
                    </td>
                    <td>
                        {availabilityWeekDay}
                    </td>
                    <td>
                        {availabilityRepeat}
                    </td>
                    <td>
                        {startDate}
                    </td>
                    <td>
                        {endDate}
                    </td>
                    <td>
                        {startTime} - {endTime}
                    </td>
                    <td>
                        {availabilityType && availabilityType.name ? availabilityType.name : ""}
                    </td>
                    <td>
                        {availability.slotTimeInMinutes}min
                    </td>
                    <td>
                        {availability.workstationCount.intern}/{availability.workstationCount.callcenter}/{availability.workstationCount.public}
                    </td>
                    <td>
                        {availability.bookable.startInDays}-{availability.bookable.endInDays}
                    </td>
                    <td>
                        {availability.description ? availability.description : '-'}
                    </td>
                </tr>
            )
        })
    }
}

TableBodyLayout.propTypes = {
    availabilities: PropTypes.array,
    onSelect: PropTypes.func.isRequired,
    onDelete: PropTypes.func.isRequired
}

export default TableBodyLayout

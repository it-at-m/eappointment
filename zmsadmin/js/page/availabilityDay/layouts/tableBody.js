import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales';
import { weekDayList, availabilitySeries, availabilityTypes, repeat } from '../helpers'
moment.locale('de')

const TableBodyLayout = (props) => {
    const { onDelete, onSelect, onAbort, availabilityList, data, showAllDates } = props;
    const [sortColumn, setSortColumn] = React.useState(null);
    const [sortDirection, setSortDirection] = React.useState('asc');

    const handleSort = (column) => {
        if (sortColumn === column) {
            setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
        } else {
            setSortColumn(column);
            setSortDirection('asc');
        }
    };

    const getSortIcon = (column) => {
        if (sortColumn !== column) {
            return <i className="fas fa-sort" aria-hidden="true"></i>;
        }
        return sortDirection === 'asc' 
            ? <i className="fas fa-sort-up" aria-hidden="true"></i>
            : <i className="fas fa-sort-down" aria-hidden="true"></i>;
    };

    const sortData = (data) => {
        if (!sortColumn) return data;

        return [...data].sort((a, b) => {
            const multiplier = sortDirection === 'asc' ? 1 : -1;
            
            switch (sortColumn) {
                case 'weekday':
                    const aWeekDay = Object.keys(a.weekday).find(key => parseInt(a.weekday[key], 10) > 0) || '';
                    const bWeekDay = Object.keys(b.weekday).find(key => parseInt(b.weekday[key], 10) > 0) || '';
                    return multiplier * aWeekDay.localeCompare(bWeekDay);
                
                case 'series':
                    const aRepeat = availabilitySeries.find(element => element.value == repeat(a.repeat))?.name || '';
                    const bRepeat = availabilitySeries.find(element => element.value == repeat(b.repeat))?.name || '';
                    return multiplier * aRepeat.localeCompare(bRepeat);
                
                case 'startDate':
                    return multiplier * (a.startDate - b.startDate);
                
                case 'endDate':
                    return multiplier * (a.endDate - b.endDate);
                
                case 'time':
                    return multiplier * a.startTime.localeCompare(b.startTime);
                
                case 'type':
                    const aType = availabilityTypes.find(element => element.value == a.type)?.name || '';
                    const bType = availabilityTypes.find(element => element.value == b.type)?.name || '';
                    return multiplier * aType.localeCompare(bType);
                
                case 'slot':
                    return multiplier * (a.slotTimeInMinutes - b.slotTimeInMinutes);
                
                case 'workstations':
                    const aTotal = a.workstationCount.intern + a.workstationCount.callcenter + a.workstationCount.public;
                    const bTotal = b.workstationCount.intern + b.workstationCount.callcenter + b.workstationCount.public;
                    return multiplier * (aTotal - bTotal);
                
                case 'booking':
                    const aBooking = a.bookable.startInDays + a.bookable.endInDays;
                    const bBooking = b.bookable.startInDays + b.bookable.endInDays;
                    return multiplier * (aBooking - bBooking);
                
                case 'description':
                    return multiplier * (a.description || '').localeCompare(b.description || '');
                
                default:
                    return 0;
            }
        });
    };

    return (
        <div className="table-responsive-wrapper"> 
            <table className="table--base">
                <thead>
                    <tr>
                        <th></th>
                        <th onClick={() => handleSort('weekday')} style={{cursor: 'pointer', whiteSpace: 'nowrap'}}>
                            Wochentage {getSortIcon('weekday')}
                        </th>
                        <th onClick={() => handleSort('series')} style={{cursor: 'pointer', whiteSpace: 'nowrap'}}>
                            Serie {getSortIcon('series')}
                        </th>
                        <th onClick={() => handleSort('startDate')} style={{cursor: 'pointer', whiteSpace: 'nowrap'}}>
                            Von {getSortIcon('startDate')}
                        </th>
                        <th onClick={() => handleSort('endDate')} style={{cursor: 'pointer', whiteSpace: 'nowrap'}}>
                            Bis {getSortIcon('endDate')}
                        </th>
                        <th onClick={() => handleSort('time')} style={{cursor: 'pointer', whiteSpace: 'nowrap'}}>
                            Uhrzeit {getSortIcon('time')}
                        </th>
                        <th onClick={() => handleSort('type')} style={{cursor: 'pointer', whiteSpace: 'nowrap'}}>
                            Typ {getSortIcon('type')}
                        </th>
                        <th onClick={() => handleSort('slot')} style={{cursor: 'pointer', whiteSpace: 'nowrap'}}>
                            Zeitschlitz {getSortIcon('slot')}
                        </th>
                        <th onClick={() => handleSort('workstations')} style={{cursor: 'pointer', whiteSpace: 'nowrap'}}>
                            Arbeitsplätze {getSortIcon('workstations')}
                        </th>
                        <th onClick={() => handleSort('booking')} style={{cursor: 'pointer', whiteSpace: 'nowrap'}}>
                            Buchung {getSortIcon('booking')}
                        </th>
                        <th onClick={() => handleSort('description')} style={{cursor: 'pointer', whiteSpace: 'nowrap'}}>
                            Anmerkung {getSortIcon('description')}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {renderTable(onDelete, onSelect, onAbort, sortData(availabilityList), data, showAllDates)}
                </tbody>
            </table>
        </div>
    )
}

/* eslint-disable complexity */
const renderTable = (onDelete, onSelect, onAbort, availabilityList, data, showAllDates) => {
    if (availabilityList.length > 0) {
        return availabilityList.map((availability, key) => {
            const startDate = moment(availability.startDate, 'X').format('DD.MM.YYYY');
            const endDate = moment(availability.endDate, 'X').format('DD.MM.YYYY');
            const startTime = moment(availability.startTime, 'h:mm:ss').format('HH:mm');
            const endTime = moment(availability.endTime, 'h:mm:ss').format('HH:mm');

            const titleEdit = `Bearbeiten von ${availability.id} (${startDate} - ${endDate})`
            const titleDelete = `Löschen von ${availability.id} (${startDate} - ${endDate})`
            const titleAbort = `Die aktuelle Beabeitung wird zurückgesetzt.`
            const titleDisabled = `Diese Aktion ist während einer aktuellen Bearbeitung nicht möglich.`

            if (!availability.id && !availability.tempId) {
                availability.tempId = `spontaneous_ID_${key}`
            }

            const onClickEdit = ev => {
                ev.preventDefault()
                onSelect(availability)
            }

            const onClickDelete = ev => {
                ev.preventDefault()
                onDelete(availability)
            }

            const onClickAbort = ev => {
                ev.preventDefault()
                onAbort()
            }

            const availabilityWeekDayList = Object.keys(availability.weekday).
                filter(key => parseInt(availability.weekday[key], 10) > 0)

            const availabilityWeekDay = weekDayList.
                filter(element => availabilityWeekDayList.includes(element.value)).map(item => item.label).join(', ')

            const availabilityRepeat = availabilitySeries.
                find(element => element.value == repeat(availability.repeat)).name

            const availabilityType = availabilityTypes.
                find(element => element.value == availability.type)

            const disabled = (
                (availability.id && availability.__modified) ||
                (availability.tempId && availability.__modified)
            );

            const isSelected = (data && (
                (data.id && availability.id == data.id) ||
                (data.tempId && availability.tempId == data.tempId))
            );

            return (
                <tr key={key} style={isSelected ? { backgroundColor: '#f9f9f9' } : null}>
                    <td className="center" style={{ "whiteSpace": "nowrap" }}>
                        <span style={{ marginRight: "5px" }}>
                            <a href="#" className="icon" aria-label="Bearbeiten" title={titleEdit} onClick={onClickEdit}>
                                <i className="fas fa-pencil-alt" aria-hidden="true"></i>
                            </a>
                        </span>
                        <span>
                            {disabled ?
                                <i className="far fa-trash-alt" title={titleDisabled}></i> :
                                <a href="#" className="icon" title={titleDelete} aria-label={titleDelete} onClick={onClickDelete}>
                                    <i className="far fa-trash-alt" aria-hidden="true"></i>
                                </a>
                            }
                        </span>
                        {disabled ?
                            <span style={{ marginLeft: "5px" }}>
                                <a href="#" className="icon" title={titleAbort} aria-label="abbrechen" onClick={onClickAbort}>
                                    <i className="fas fa-ban" aria-hidden="true"></i>
                                </a>
                            </span>
                            : null
                        }
                    </td>
                    <td>{availabilityWeekDay}</td>
                    <td>{availabilityRepeat}</td>
                    <td>{startDate}</td>
                    <td>{endDate}</td>
                    <td>{startTime} - {endTime}</td>
                    <td>{availabilityType && availabilityType.name ? availabilityType.name : ""}</td>
                    <td>{availability.slotTimeInMinutes}min</td>
                    <td>
                        {availability.workstationCount.intern}/
                        {availability.workstationCount.callcenter}/
                        {availability.workstationCount.public}
                    </td>
                    <td>{availability.bookable.startInDays}-{availability.bookable.endInDays}</td>
                    <td>{availability.description ? availability.description : '-'}</td>
                </tr>
            )
        })
    }
}

TableBodyLayout.propTypes = {
    availabilityList: PropTypes.array,
    data: PropTypes.object,
    onSelect: PropTypes.func.isRequired,
    onDelete: PropTypes.func.isRequired,
    onAbort: PropTypes.func.isRequired,
    showAllDates: PropTypes.bool
}

export default TableBodyLayout

import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment/min/moment-with-locales';
import { weekDayList, availabilitySeries, availabilityTypes, repeat } from '../helpers'
moment.locale('de')

const TableBodyLayout = (props) => {
    const { onDelete, onSelect, onAbort, availabilityList, data, showAllDates } = props;
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
                    {renderTable(onDelete, onSelect, onAbort, availabilityList, data, showAllDates)}
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

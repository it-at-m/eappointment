import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'

import propTypeAvailability from '../../../../lib/propTypeAvailability'
import { timeToFloat } from '../../../../lib/utils'

const Appointment = props => {
    const { data, maxWorkstationCount, onSelect } = props
    const heightEm = maxWorkstationCount > 0
                   ? data.workstationCount.intern * 0.8 / maxWorkstationCount
                   : 0

    const timeItemStart = timeToFloat(data.startTime)
    const timeItemEnd = timeToFloat(data.endTime)
    const timeItemLength = timeItemEnd - timeItemStart

    const startTime = moment(data.startTime, 'hh:mm:ss').format('HH:mm');
    const endTime = moment(data.endTime, 'hh:mm:ss').format('HH:mm');

    const description = (data.description) ? `${data.description}, ` : ``;
    const title = `${description}${startTime} - ${endTime} - Insgesamt:${data.workstationCount.intern} / Callcenter: ${data.workstationCount.callcenter} / Internet: ${data.workstationCount.public}`

    const style = {
        height: `${heightEm}em`,
        left: `${timeItemStart}em`,
        width: `${timeItemLength}em`
    }

    const onClick = ev => {
        ev.preventDefault()
        onSelect(data)
    }

    return (
        <a href="#" className="item-bar" {...{ title, style, onClick }}>
            <span className="item-bar_inner"><i className="fas fa-user-alt" aria-hidden="true"></i> { data.workstationCount.intern }</span>
        </a>
    )
}

Appointment.propTypes = {
    data: propTypeAvailability,
    maxWorkstationCount: PropTypes.number,
    onSelect: PropTypes.func.isRequired
}

export default Appointment

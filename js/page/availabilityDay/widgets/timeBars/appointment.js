import React, { PropTypes } from 'react'
import { timeToFloat } from '../../../../lib/utils'

const Appointment = props => {
    const { data, maxWorkstationCount, onSelect } = props

    const heightEm = maxWorkstationCount > 0
                   ? data.workstationCount.intern * 0.7 / maxWorkstationCount
                   : 0

    const timeItemStart = timeToFloat(data.startTime)
    const timeItemEnd = timeToFloat(data.endTime)
    const timeItemLength = timeItemEnd - timeItemStart

    const title = `${data.description}, ${data.startTime} - ${data.endTime} - Intern:${data.workstationCount.intern} / Public: ${data.workstationCount.public} / Callcenter: ${data.workstationCount.callcenter}`

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
            <span className="item-bar_inner">👤 { data.workstationCount.intern }</span>
        </a>
    )
}

Appointment.propTypes = {
    data: PropTypes.shape({
        type: PropTypes.oneOf(['appointment'])
    }),
    maxWorkstationCount: PropTypes.number,
    onSelect: PropTypes.func.isRequired
}

export default Appointment

import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'

import propTypeAvailability from '../../../../lib/propTypeAvailability'
import { timeToFloat } from '../../../../lib/utils'

const Opening = props => {
    const { data, onSelect, lane } = props
    const startTime = moment(data.startTime, 'hh:mm:ss').format('HH:mm');
    const endTime = moment(data.endTime, 'hh:mm:ss').format('HH:mm');
    const description = (data.description) ? `${data.description}, ` : ``;
    const title = `${description}${startTime} - ${endTime}`

    const timeItemStart = timeToFloat(data.startTime)
    const timeItemEnd = timeToFloat(data.endTime)
    const timeItemLength = timeItemEnd - timeItemStart

    const style = {
        left: `${timeItemStart}em`,
        width: `${timeItemLength}em`,
        bottom: `${-lane * 1.8}rem`
    }

    const onClick = ev => {
        ev.preventDefault()
        onSelect(data)
    }

    return (
        <a href="#" className="item-bar" {... { title, style, onClick }}>
            <span className="item-bar_inner"></span>
        </a>
    )
}

Opening.defaultProps = {
    lane: 0
}

Opening.propTypes = {
    data: propTypeAvailability,
    onSelect: PropTypes.func.isRequired,
    lane: PropTypes.number
}

export default Opening

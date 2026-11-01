import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'

import propTypeAvailability from '../../../../lib/propTypeAvailability'
import { timeToFloat } from '../../../../lib/utils'

// Lanes stack below the first one, the graph grows by getLaneExtra to make room for them
const LANE_SPACING = 1.8
const BAR_HEIGHT = 1.5

export const getLaneExtra = laneCount => Math.max(laneCount - 1, 0) * LANE_SPACING

const Opening = props => {
    const { data, onSelect, lane = 0 } = props
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
        bottom: `${-lane * LANE_SPACING}rem`,
        borderTopWidth: `${BAR_HEIGHT}rem`
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

Opening.propTypes = {
    data: propTypeAvailability,
    onSelect: PropTypes.func.isRequired,
    lane: PropTypes.number
}

export default Opening

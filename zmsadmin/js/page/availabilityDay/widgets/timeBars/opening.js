import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'

import propTypeAvailability from '../../../../lib/propTypeAvailability'
import { timeToFloat } from '../../../../lib/utils'

// Lanes stack below the first one, the timetable container keeps its fixed height
const LANE_SPACING = 1.8
const LANE_GAP = 0.3
const BAR_HEIGHT = 1.5
const MAX_LANE_OFFSET = 5.5

export const getLaneLayout = (lane, laneCount) => {
    const spacing = laneCount > 1 ? Math.min(LANE_SPACING, MAX_LANE_OFFSET / (laneCount - 1)) : LANE_SPACING
    return {
        offset: lane * spacing,
        barHeight: Math.min(BAR_HEIGHT, spacing - LANE_GAP)
    }
}

const Opening = props => {
    const { data, onSelect, lane = 0, laneCount = 1 } = props
    const startTime = moment(data.startTime, 'hh:mm:ss').format('HH:mm');
    const endTime = moment(data.endTime, 'hh:mm:ss').format('HH:mm');
    const description = (data.description) ? `${data.description}, ` : ``;
    const title = `${description}${startTime} - ${endTime}`

    const timeItemStart = timeToFloat(data.startTime)
    const timeItemEnd = timeToFloat(data.endTime)
    const timeItemLength = timeItemEnd - timeItemStart
    const { offset, barHeight } = getLaneLayout(lane, laneCount)

    const style = {
        left: `${timeItemStart}em`,
        width: `${timeItemLength}em`,
        bottom: `${-offset}rem`,
        borderTopWidth: `${barHeight}rem`
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
    lane: PropTypes.number,
    laneCount: PropTypes.number
}

export default Opening

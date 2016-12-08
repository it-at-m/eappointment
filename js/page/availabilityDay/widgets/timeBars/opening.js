import React, { PropTypes } from 'react'
import { timeToFloat } from '../../../../lib/utils'

const Opening = props => {
    console.log('OpeningTimeBar', props)
    const { data, onSelect } = props
    const title = `${data.description}, ${data.startTime} - ${data.endTime}`

    const timeItemStart = timeToFloat(data.startTime)
    const timeItemEnd = timeToFloat(data.endTime)
    const timeItemLength = timeItemEnd - timeItemStart

    const style = {
        left: `${timeItemStart}em`,
        width: `${timeItemLength}em`
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
    data: PropTypes.shape({
        type: PropTypes.oneOf(['openinghours'])
    }),
    onSelect: PropTypes.func.isRequried
}

export default Opening

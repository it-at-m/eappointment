import React from 'react'
import PropTypes from 'prop-types'



const PageLayout = (props) => {
    return (
        <div>
            {props.conflicts}
            {props.tabs}
            {props.timeTable}
            {props.saveBarTop}
            {props.accordion}
            {props.saveBarBottom}
        </div>
    )
}

PageLayout.propTypes = {
    timeTable: PropTypes.node,
    accordion: PropTypes.node,
    conflicts: PropTypes.node,
    tabs: PropTypes.node,
    saveBarTop: PropTypes.node,
    saveBarBottom: PropTypes.node
}

export default PageLayout

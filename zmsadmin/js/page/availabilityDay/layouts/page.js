import React from 'react'
import PropTypes from 'prop-types'



const PageLayout = (props) => {
    return (
        <div>
            {props.conflicts}
            {props.tabs}
            {props.timeTable}
            {props.saveBar}
            {props.accordion}
        </div>
    )
}

PageLayout.propTypes = {
    timeTable: PropTypes.node,
    accordion: PropTypes.node,
    conflicts: PropTypes.node,
    tabs: PropTypes.node,
    saveBar: PropTypes.node
}

export default PageLayout

import React from 'react'
import PropTypes from 'prop-types'



const PageLayout = (props) => {
    return (
        <div>
            {props.saveBar}
            {props.conflicts}
            {props.tabs}
            {props.timeTable}
            {props.updateBar}
            {props.form}
        </div>
    )
}

PageLayout.propTypes = {
    timeTable: PropTypes.node,
    form: PropTypes.node,
    conflicts: PropTypes.node,
    tabs: PropTypes.node,
    saveBar: PropTypes.node,
    updateBar: PropTypes.node
}

export default PageLayout

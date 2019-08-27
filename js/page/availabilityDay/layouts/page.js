import React, { PropTypes } from 'react'

const PageLayout = (props) => {
    return (
        <div>
            {props.saveBar}
            {props.conflicts}
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
    saveBar: PropTypes.node,
    updateBar: PropTypes.node
}

export default PageLayout

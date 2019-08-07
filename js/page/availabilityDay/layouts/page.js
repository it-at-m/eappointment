import React, { PropTypes } from 'react'

const PageLayout = (props) => {
    return (
        <div>
            {props.saveBar}
            {props.timeTable}
            {props.updateBar}
            <div className="grid"> 
                <div className="grid__item two-thirds">
                    {props.form}
                </div>
                <div className="grid__item one-third">
                    {props.conflicts}
                </div>
            </div>
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

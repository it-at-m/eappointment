import React, { PropTypes } from 'react'

const PageLayout = (props) => {
    return (
        <div>
            {props.timeTable}
            <div className="lineup lineup--availability"> 
                <div className="lineup_actor lineup_actor--left">
                    {props.form}
                </div>
                <div className="lineup_actor lineup_actor--right">
                    {props.conflicts}
                </div>
            </div>
        </div>
    )
}

PageLayout.propTypes = {
    timeTable: PropTypes.node,
    form: PropTypes.node,
    conflicts: PropTypes.node
}

export default PageLayout

import React, { PropTypes } from 'react'

const hours = (() => {
    const hours = []
    for (let i = 0; i < 24; i = i + 1) {
        hours.push(i)
    }
    return hours
})()

const background = () => {
    return <div className="background-level">
        {hours.map(i => (
            <div className="time-level_item" style={{ left: `${i}em` }}>
                <div className="time-level_item--top">
                    <span className="text">{i}:00</span>
                </div>
                <div className="time-level_item--bottom">
                    <span className="text">{i}:00</span>
                </div>
            </div>
         ))}
    </div>
}

const TimeTableBodyLayout = (props) => {
    return (
        <div className="grid">
            <div className="grid__item one-tenth">
                <div className="availability-timetable_legend">
                  { props.showConflicts ? <div className="legend__item legend_conflict">Konflikte</div> : null }
                    <div className="legend__item legend_numberofappointment">Freie Slots</div>
                    <div className="legend__item legend_appointment">Termin&shy;kunden</div>
                    <div className="legend__item legend_opening">Spontan&shy;kunden</div>
                </div>
            </div>
            <div className="grid__item nine-tenths">
                <div className="availability-timetable_container" id="js-timetable" style={{fontSize: "70px"}} >
                    <div className="inner">
                      {background()}
                      {props.showConflicts ? props.conflicts : null}
                    </div>
                </div>
            </div>
        </div>
    )
}

TimeTableBodyLayout.propTypes = {
    conflicts: PropTypes.node,
    showConflicts: PropTypes.bool
}

export default TimeTableBodyLayout

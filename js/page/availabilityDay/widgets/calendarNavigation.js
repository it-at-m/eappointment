import React from 'react'

const calendarNavigation = (links) => {

    return (
        <div className="calendar-navigation">
            {(links.previousDay >= links.sameDay) &&  
                    <a href={links.previousDay} title="Tag zurück" className="icon prev"><i className="fas fa-caret-square-left" aria-hidden="true"></i> <span className="aural">Tag zurück</span></a>
            }
            {(links.previousDay >= links.sameDay) ||  
                    <span title="Tag zurück nicht möglich" className="icon prev inactive"><i className="fas fa-caret-square-left color-text-disabled"></i></span>
            }
            &nbsp;<a href={links.sameDay} className="today">Heute</a>&nbsp;
            <a href={links.nextDay} title="Tag vor" className="icon next"><i className="fas fa-caret-square-right" aria-hidden="true"></i> <span className="aural">Tag vor</span></a>
        </div>
        
    )
}

export default calendarNavigation

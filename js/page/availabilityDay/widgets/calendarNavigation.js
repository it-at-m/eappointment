import React from 'react'

const calendarNavigation = (links) => {

    return (
        <div className="calendar-navigation">
            {(links.previousDay >= links.sameDay) &&  
                    <a href={links.previousDay} title="Tag zurück" className="icon prev">◀</a>
            }
            <a href={links.sameDay} className="today">Heute</a>
            <a href={links.nextDay} title="Tag vor" className="icon next">▶</a> 
        </div>
    )
}

export default calendarNavigation

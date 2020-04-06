import React from 'react'

import moment from 'moment'

const formatDate = date => {
    const momentDate = moment(date)
    return `${momentDate.format('DD.MM.YYYY')} um ${momentDate.format('HH:mm')} Uhr`
}

const SaveBar = (props) => {
    return (
        <div className="message message--success">
            Öffnungszeiten gespeichert, {formatDate(props.lastSave)}
        </div>
    )
}

export default SaveBar

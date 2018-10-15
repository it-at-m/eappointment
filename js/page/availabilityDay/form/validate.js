const validate = data => {
    let valid = true
    const errors = {}

    if (!data.type) {
        errors.type = 'Typ erforderlich'
        valid = false
    }

    // Validate openning times 
    var startHour = data.startTime.split(':')[0]
    var endHour = data.endTime.split(':')[0]
    var startMinute = data.startTime.split(':')[1]
    var endMinute = data.endTime.split(':')[1]
    var slotTime = data.slotTimeInMinutes

    if (startHour >= endHour) {      
        errors.startTime = 'Endzeit muss nach Startzeit stattfinden'
        valid = false      
    }

    if ((endMinute - startMinute) % slotTime > 0) {
        errors.type = 'Öffnungszeit kann nicht in Zeitschlitze verteilt werden.'
        valid = false      
    }

    return {
        valid,
        errors
    }
}

export default validate

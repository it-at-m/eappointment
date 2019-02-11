import moment from 'moment'

const validate = (data, today) => {
    const errors = {}

    if (!data.type) {
        errors.type = 'Typ erforderlich'
    }

    // Validate openning times 
    var startHour = data.startTime.split(':')[0]
    var endHour = data.endTime.split(':')[0]
    var startMinute = data.startTime.split(':')[1]
    var endMinute = data.endTime.split(':')[1]

    var startTime = moment(data.startDate, 'X').set({ h: startHour, m: startMinute }).unix();
    var endTime = moment(data.endDate, 'X').set({ h: endHour, m: endMinute }).unix();
    var slotTime = data.slotTimeInMinutes

    if (startTime < today && endTime < today) {
        errors.startTime = 'Öffnungszeiten in der Vergangenheit lassen sich nicht bearbeiten'
    }

    if (startTime >= endTime || startHour >= endHour) {
        errors.startTime = 'Das Terminende muss nach dem Terminanfang sein'
    }

    if ((endTime - startTime) / 60 % slotTime > 0) {
        errors.type = 'Zeitschlitze müssen sich gleichmäßig in der Öffnungszeit aufteilen lassen'
    }

    let valid = (Object.keys(errors).length) ? false : true

    return {
        valid,
        errors
    }
}

export default validate

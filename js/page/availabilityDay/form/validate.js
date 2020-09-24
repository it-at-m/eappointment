import moment from 'moment'

const validate = (data, props) => {
    const errors = {}
    const today = moment(props.today, 'X')
    const selected = moment(props.timestamp, 'X')
    const yesterday = selected.startOf('day').clone().subtract(1, 'days')
    const tomorrow = selected.startOf('day').clone().add(1, 'days')

    const startHour = data.startTime.split(':')[0]
    const endHour = data.endTime.split(':')[0]

    const startMinute = data.startTime.split(':')[1]
    const endMinute = data.endTime.split(':')[1]

    const todayTime = today.unix();
    const startTime = moment(data.startDate, 'X');
    const endTime = moment(data.endDate, 'X');
    //const startDateTime = startTime.set({ h: startHour, m: startMinute });
    const endDateTime = endTime.set({ h: endHour, m: endMinute });
    const startTimestamp = startTime.set({ h: startHour, m: startMinute }).unix();
    const endTimestamp = endTime.set({ h: endHour, m: endMinute }).unix();
    const slotTime = data.slotTimeInMinutes

    if (!data.type) {
        errors.type = 'Typ erforderlich'
    }

    if (endTimestamp < todayTime) {
        errors.startTime = 'Öffnungszeiten in der Vergangenheit lassen sich nicht bearbeiten '
            + '(Die aktuelle Zeit "'+today.format('DD.MM.YYYY HH:mm')+' Uhr" liegt nach dem Terminende am "'+endDateTime.format('DD.MM.YYYY HH:mm')+' Uhr").'
    }

    const dayMinutesStart = (parseInt(startHour) * 60) + parseInt(startMinute);
    const dayMinutesEnd = (parseInt(endHour) * 60) + parseInt(endMinute);

    if (dayMinutesEnd <= dayMinutesStart) {
        errors.endTime = 'Die Uhrzeit "von" muss kleiner der Uhrzeit "bis" sein.'
    } else if (startTimestamp >= endTimestamp) {
        errors.endTime = 'Das Startdatum muss nach dem Enddatum sein.'
    }

    if ((startHour == "00" && startMinute == "00") || (endHour == "00" && endMinute == "00")) {
        errors.type = 'Die Uhrzeit darf nicht 00:00 sein.'
    }

    if ((endTimestamp - startTimestamp) / 60 % slotTime > 0) {
        errors.type = 'Zeitschlitze müssen sich gleichmäßig in der Öffnungszeit aufteilen lassen.'
    }

    if (selected.unix() > todayTime && startTime.startOf('day').isAfter(selected.startOf('day'), 'day')) {
        errors.startTimeFuture = `Das Startdatum der Öffnungszeit muss vor dem ${tomorrow.format('DD.MM.YYYY')} liegen.`
    }

    if (selected.unix() > todayTime && endTime.startOf('day').isBefore(selected.startOf('day'), 'day')) {
        errors.endTimeFuture = `Das Enddatum der Öffnungszeit muss nach dem ${yesterday.format('DD.MM.YYYY')} liegen.`
    }

    let valid = (0 < Object.keys(errors).length) ? false : true

    if (!valid) {
        errors.id = data.id || data.tempId
    }
    return {
        valid,
        errors
    }
}

export default validate;

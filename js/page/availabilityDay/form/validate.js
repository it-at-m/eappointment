import moment from 'moment'

const validate = (data, props) => {
    const today = moment(props.today, 'X')
    const selectedDate = moment(props.timestamp, 'X')
    const yesterday = selectedDate.startOf('day').clone().subtract(1, 'days')
    const tomorrow = selectedDate.startOf('day').clone().add(1, 'days')
    
    let errorList = {
        id: data.id || data.tempId,
        itemList: []
    }

    errorList.itemList.push(validateStartTime(today, tomorrow, selectedDate, data))
    errorList.itemList.push(validateEndTime(today, yesterday, selectedDate, data))
    errorList.itemList.push(validateType(data))
    errorList.itemList.push(validateSlotTime(data))
    
    errorList.itemList = errorList.itemList.filter(el => el.length);
    let valid = (0 < errorList.itemList.length) ? false : true

    return {
        valid,
        errorList
    }
}

function validateStartTime(today, tomorrow, selectedDate, data) {
    let errorList = []
    const startTime = moment(data.startDate, 'X').startOf('day');
    const endTime = moment(data.endDate, 'X');
    const endDateTime = endTime.set({ h: endHour, m: endMinute });
    const endTimestamp = endTime.set({ h: endHour, m: endMinute }).unix();
    const startHour = data.startTime.split(':')[0]
    const endHour = data.endTime.split(':')[0]
    const startMinute = data.startTime.split(':')[1]
    const endMinute = data.endTime.split(':')[1]

    if (endTimestamp < today.unix()) {
        errorList.push({
            type: 'startTime', 
            message: 'Öffnungszeiten in der Vergangenheit lassen sich nicht bearbeiten '
            + '(Die aktuelle Zeit "'+today.format('DD.MM.YYYY HH:mm')+' Uhr" liegt nach dem Terminende am "'+endDateTime.format('DD.MM.YYYY HH:mm')+' Uhr").'
        })
    }

    if (selectedDate.unix() > today.unix() && startTime.isAfter(selectedDate.startOf('day'), 'day')) {
        errorList.push({
            type: 'startTimeFuture', 
            message: `Das Startdatum der Öffnungszeit muss vor dem ${tomorrow.format('DD.MM.YYYY')} liegen.`
        })
    }

    if ((startHour == "00" && startMinute == "00") || (endHour == "00" && endMinute == "00")) {
        errorList.push({
            type: 'startOfDay',
            message: 'Die Uhrzeit darf nicht "00:00" sein.'
        })
    }
    return errorList;
}

function validateEndTime(today, yesterday, selectedDate, data) {
    var errorList = []
    const startTime = moment(data.startDate, 'X').startOf('day');
    const endTime = moment(data.endDate, 'X').startOf('day');
    const startHour = data.startTime.split(':')[0]
    const endHour = data.endTime.split(':')[0]
    const startMinute = data.startTime.split(':')[1]
    const endMinute = data.endTime.split(':')[1]
    const dayMinutesStart = (parseInt(startHour) * 60) + parseInt(startMinute);
    const dayMinutesEnd = (parseInt(endHour) * 60) + parseInt(endMinute);
    const startTimestamp = startTime.set({ h: startHour, m: startMinute }).unix();
    const endTimestamp = endTime.set({ h: endHour, m: endMinute }).unix();

    if (dayMinutesEnd <= dayMinutesStart) {
        errorList.push({
            type: 'endTime', 
            message: 'Die Uhrzeit "von" muss kleiner der Uhrzeit "bis" sein.'
        })
    } else if (startTimestamp >= endTimestamp) {
        errorList.push({
            type: 'endTime', 
            message: 'Das Startdatum muss nach dem Enddatum sein.'
        })
    }

    if (selectedDate.unix() > today.unix() && endTime.isBefore(selectedDate.startOf('day'), 'day')) {
        errorList.push({
            type: 'endTimeFuture',
            message: `Das Enddatum der Öffnungszeit muss nach dem ${yesterday.format('DD.MM.YYYY')} liegen.`
        })
    }
    return errorList;
}

function validateType(data) {
    let errorList = []
    if (!data.type) {
        errorList.push({
            type: 'type',
            message: 'Typ erforderlich'
        })
    }
    return errorList;
}

function validateSlotTime (data) {
    let errorList = []
    const startTime = moment(data.startDate, 'X');
    const endTime = moment(data.endDate, 'X');
    const startHour = data.startTime.split(':')[0]
    const endHour = data.endTime.split(':')[0]
    const startMinute = data.startTime.split(':')[1]
    const endMinute = data.endTime.split(':')[1]
    const startTimestamp = startTime.set({ h: startHour, m: startMinute }).unix();
    const endTimestamp = endTime.set({ h: endHour, m: endMinute }).unix();
    const slotTime = data.slotTimeInMinutes

    if ((endTimestamp - startTimestamp) / 60 % slotTime > 0) {
        errorList.push({
            type: 'slotCount',
            message: 'Zeitschlitze müssen sich gleichmäßig in der Öffnungszeit aufteilen lassen.'
        })
    }
    return errorList;
}

export default validate;

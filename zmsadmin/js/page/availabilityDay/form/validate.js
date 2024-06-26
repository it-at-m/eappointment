import moment from 'moment'

const validate = (data, props) => {
    const currentTime = new Date()
    const today = moment(props.today, 'X')
    today.set('hour', currentTime.getHours())
    today.set('minute', currentTime.getMinutes())
    today.set('second', currentTime.getSeconds())

    const selectedDate = moment(props.timestamp, 'X')
    const yesterday = selectedDate.startOf('day').clone().subtract(1, 'days')
    const tomorrow = selectedDate.startOf('day').clone().add(1, 'days')
    
    let errorList = {
        id: data.id || data.tempId,
        itemList: []
    }

    errorList.itemList.push(validateStartTime(today, tomorrow, selectedDate, data))
    errorList.itemList.push(validateEndTime(today, yesterday, selectedDate, data))
    errorList.itemList.push(validateOriginEndTime(today, yesterday, selectedDate, data))
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
    const startHour = data.startTime.split(':')[0];
    const endHour = data.endTime.split(':')[0];
    const startMinute = data.startTime.split(':')[1];
    const endMinute = data.endTime.split(':')[1];
    //const startDateTime = startTime.clone().set({ h: startHour, m: startMinute });
    const isFuture = (data.kind && 'future' == data.kind);
    //const isOrigin = (data.kind && 'origin' == data.kind);

    if (! isFuture && selectedDate.unix() > today.unix() && startTime.isAfter(selectedDate.startOf('day'), 'day')) {
        errorList.push({
            type: 'startTimeFuture', 
            message: `Das Startdatum der Öffnungszeit muss vor dem ${tomorrow.format('DD.MM.YYYY')} liegen.`
        })
    }
/*
    if (isOrigin && startTime.isBefore(today.startOf('day'), 'day') && data.__modified) {
        errorList.push({
            type: 'startTimeOrigin', 
            message: 'Öffnungszeiten in der Vergangenheit lassen sich nicht bearbeiten '
            + '(Der Terminanfang am "'+startDateTime.format('DD.MM.YYYY')+' liegt vor dem heutigen Tag").'
        })
    }
*/

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
    const endTimestamp = endTime.clone().set({ h: endHour, m: endMinute }).unix();

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
    return errorList;
}

function validateOriginEndTime(today, yesterday, selectedDate, data) {
    var errorList = []
    const endTime = moment(data.endDate, 'X').startOf('day');
    const endHour = data.endTime.split(':')[0]
    const endMinute = data.endTime.split(':')[1]
    const endDateTime = endTime.clone().set({ h: endHour, m: endMinute });
    const endTimestamp = endTime.clone().set({ h: endHour, m: endMinute }).unix();
    const isOrigin = (data.kind && 'origin' == data.kind)

    if (! isOrigin && selectedDate.unix() > today.unix() && endTime.isBefore(selectedDate.startOf('day'), 'day')) {
        errorList.push({
            type: 'endTimeFuture',
            message: `Das Enddatum der Öffnungszeit muss nach dem ${yesterday.format('DD.MM.YYYY')} liegen.`
        })
    }

    if (
        (! isOrigin && endTimestamp < today.unix()) 
    ) {
        errorList.push({
            type: 'endTimePast', 
            message: 'Öffnungszeiten in der Vergangenheit lassen sich nicht bearbeiten '
            + '(Die aktuelle Zeit "'+today.format('DD.MM.YYYY HH:mm')+' Uhr" liegt nach dem Terminende am "'+endDateTime.format('DD.MM.YYYY HH:mm')+' Uhr").'
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
    const startHour = data.startTime.split(':')[0]
    const endHour = data.endTime.split(':')[0]
    const startMinute = data.startTime.split(':')[1]
    const endMinute = data.endTime.split(':')[1]
    const startTimestamp = startTime.set({ h: startHour, m: startMinute }).unix();
    const endTimestamp = startTime.set({ h: endHour, m: endMinute }).unix();
    const slotTime = data.slotTimeInMinutes

    let slotAmount = (endTimestamp - startTimestamp) / 60 % slotTime;
    if (slotAmount > 0) {
        errorList.push({
            type: 'slotCount',
            message: 'Zeitschlitze müssen sich gleichmäßig in der Öffnungszeit aufteilen lassen.'
        })
    }
    return errorList;
}

export default validate;

export function hasSlotCountError(dataObject) {
    const errorList = dataObject?.errorList;

    for (let key in errorList) {
        if (errorList.hasOwnProperty(key)) {
            const error = errorList[key];
            if (error && Array.isArray(error.itemList)) {
                for (let sublist of error.itemList) {
                    if (Array.isArray(sublist)) {
                        for (let item of sublist) {
                            if (item && item.type === 'slotCount') {
                                return true;
                            }
                        }
                    }
                }
            }
        }
    }
    return false;
}

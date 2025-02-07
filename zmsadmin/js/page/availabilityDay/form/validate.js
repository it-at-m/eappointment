import moment from 'moment';

const validate = (data, props) => {
    const currentTime = new Date();
    const today = moment(props.today, 'X');
    today.set('hour', currentTime.getHours());
    today.set('minute', currentTime.getMinutes());
    today.set('second', currentTime.getSeconds());

    const selectedDate = moment(props.timestamp, 'X');
    const yesterday = selectedDate.startOf('day').clone().subtract(1, 'days');
    const tomorrow = selectedDate.startOf('day').clone().add(1, 'days');

    let errorList = {
        id: data.id || data.tempId,
        itemList: []
    };

    errorList.itemList.push(validateNullValues(data));
    errorList.itemList.push(validateTimestampAndTimeFormats(data));
    errorList.itemList.push(validateWeekdays(data));
    errorList.itemList.push(validateStartTime(today, tomorrow, selectedDate, data));
    errorList.itemList.push(validateEndTime(today, yesterday, selectedDate, data));
    errorList.itemList.push(validateOriginEndTime(today, yesterday, selectedDate, data));
    errorList.itemList.push(validateType(data));
    errorList.itemList.push(validateSlotTime(data));
    errorList.itemList.push(validateBookableDayRange(data));

    errorList.itemList = errorList.itemList.filter(el => el.length);
    let valid = (0 < errorList.itemList.length) ? false : true;

    return {
        valid,
        errorList
    };
};

function validateWeekdays(data) {
    let errorList = [];
    
    // Ensure weekday object exists
    if (!data.weekday) {
        errorList.push({
            type: 'weekdayRequired',
            message: 'Mindestens ein Wochentag muss ausgewählt sein.'
        });
        return errorList;
    }
    
    // Check if at least one weekday is selected using bitmap values
    const hasSelectedDay = Object.values(data.weekday)
        .some(value => parseInt(value || '0') > 0);
    
    if (!hasSelectedDay) {
        errorList.push({
            type: 'weekdayRequired',
            message: 'Mindestens ein Wochentag muss ausgewählt sein.'
        });
        return errorList;
    }

    const weekdayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    const germanWeekdays = {
        'sunday': 'Sonntag',
        'monday': 'Montag',
        'tuesday': 'Dienstag',
        'wednesday': 'Mittwoch',
        'thursday': 'Donnerstag',
        'friday': 'Freitag',
        'saturday': 'Samstag'
    };

    // Track which selected weekdays appear in the range
    const selectedWeekdays = weekdayNames.filter(day => parseInt(data.weekday[day] || '0') > 0);
    const foundWeekdays = new Set();

    // Check if dates fall on selected weekdays
    const startDate = moment.unix(data.startDate);
    const endDate = moment.unix(data.endDate);
    const currentDate = startDate.clone();

    while (currentDate <= endDate) {
        const dayIndex = currentDate.day();
        const weekDayName = weekdayNames[dayIndex];
        const weekdayValue = parseInt(data.weekday[weekDayName] || '0');
        
        if (weekdayValue > 0) {
            foundWeekdays.add(weekDayName);
        }
    
        currentDate.add(1, 'day');
    }

    // Check if any selected weekday doesn't appear in the range
    const unusedWeekdays = selectedWeekdays.filter(day => !foundWeekdays.has(day));
    if (unusedWeekdays.length > 0) {
        errorList.push({
            type: 'invalidWeekday',
            message: `Die ausgewählten Wochentage (${unusedWeekdays.map(day => germanWeekdays[day]).join(', ')}) kommen im gewählten Zeitraum nicht vor.`
        });
    }

    return errorList;
}

function validateNullValues(data) {
    let errorList = [];

    if (!data.startDate) {
        errorList.push({
            type: 'startDateNull',
            message: 'Das Startdatum darf nicht leer sein.'
        });
    }

    if (!data.endDate) {
        errorList.push({
            type: 'endDateNull',
            message: 'Das Enddatum darf nicht leer sein.'
        });
    }

    if (!data.startTime) {
        errorList.push({
            type: 'startTimeNull',
            message: 'Die Startzeit darf nicht leer sein.'
        });
    }

    if (!data.endTime) {
        errorList.push({
            type: 'endTimeNull',
            message: 'Die Endzeit darf nicht leer sein.'
        });
    }

    return errorList;
}

function validateBookableDayRange(data) {
    const errorList = [];

    const startInDays = parseInt(data.bookable.startInDays, 10);
    const endInDays = parseInt(data.bookable.endInDays, 10);

    if (startInDays > endInDays) {
        errorList.push({
            type: 'bookableDayRange',
            message: 'Bitte geben Sie im Feld \'von\' eine kleinere Zahl ein als im Feld \'bis\', wenn Sie bei \'Buchbar\' sind.'
        });
    }

    return errorList;
}

function validateTimestampAndTimeFormats(data) {
    let errorList = [];
    const timeRegex = /^\d{2}:\d{2}(:\d{2})?$/;

    let isStartDateValid = isValidTimestamp(data.startDate);
    let isEndDateValid = isValidTimestamp(data.endDate);

    if (!isStartDateValid) {
        errorList.push({
            type: 'startDateInvalid',
            message: 'Das Startdatum ist kein gültiger Zeitstempel.'
        });
    }

    if (!isEndDateValid) {
        errorList.push({
            type: 'endDateInvalid',
            message: 'Das Enddatum ist kein gültiger Zeitstempel.'
        });
    }

    if (data.startTime) {
        if (!timeRegex.test(data.startTime)) {
            errorList.push({
                type: 'startTimeFormat',
                message: 'Die Startzeit muss im Format "HH:mm:ss" oder "HH:mm" vorliegen.'
            });
        }
    } else {
        errorList.push({
            type: 'startTimeMissing',
            message: 'Die Startzeit darf nicht leer sein.'
        });
    }

    if (data.endTime) {
        if (!timeRegex.test(data.endTime)) {
            errorList.push({
                type: 'endTimeFormat',
                message: 'Die Endzeit muss im Format "HH:mm:ss" oder "HH:mm" vorliegen.'
            });
        }
    } else {
        errorList.push({
            type: 'endTimeMissing',
            message: 'Die Endzeit darf nicht leer sein.'
        });
    }

    if (isStartDateValid && isEndDateValid) {
        if (new Date(data.startDate) > new Date(data.endDate)) {
            errorList.push({
                type: 'dateOrderInvalid',
                message: 'Das Startdatum darf nicht nach dem Enddatum liegen.'
            });
        }
    }

    return errorList;
}

function isValidTimestamp(timestamp) {
    return !Number.isNaN(Number(timestamp)) && moment.unix(timestamp).isValid();
}

function parseTimestampAndTime(dateTimestamp, timeStr) {
    const date = moment.unix(dateTimestamp);
    if (!date.isValid()) return null;

    const [hours, minutes, seconds] = timeStr.split(':').map((val, index) => parseInt(val || 0));
    return date.set({ hour: hours, minute: minutes, second: seconds });
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

    if (!isFuture && selectedDate.unix() > today.unix() && startTime.isAfter(selectedDate.startOf('day'), 'day')) {
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
    const startHourInt = parseInt(startHour);
    const endHourInt = parseInt(endHour);
    if (
        (startHourInt === 22 && startMinute > 0) || 
        startHourInt === 23 || 
        startHourInt === 0 || 
        (endHourInt === 22 && endMinute > 0) || 
        endHourInt === 23 || 
        endHourInt === 0 ||
        (startHourInt === 1 && startMinute > 0) ||
        (endHourInt === 1 && endMinute > 0)
    ) {
        errorList.push({
            type: 'startOfDay',
            message: 'Die Uhrzeit darf nicht zwischen 22:00 und 01:00 liegen, da in diesem Zeitraum der tägliche Cronjob ausgeführt wird.'
        });
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
            message: 'Die Endzeit darf nicht vor der Startzeit liegen.'
        })
    }

    if (startTimestamp >= endTimestamp) {
        errorList.push({
            type: 'endTime',
            message: 'Das Enddatum darf nicht vor dem Startdatum liegen.'
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

    if (!isOrigin && selectedDate.unix() > today.unix() && endTime.isBefore(selectedDate.startOf('day'), 'day')) {
        errorList.push({
            type: 'endTimeFuture',
            message: `Das Enddatum der Öffnungszeit muss nach dem ${yesterday.format('DD.MM.YYYY')} liegen.`
        })
    }

    if (
        (!isOrigin && endTimestamp < today.unix())
    ) {
        errorList.push({
            type: 'endTimePast',
            message: 'Öffnungszeiten in der Vergangenheit lassen sich nicht bearbeiten '
                + '(Die aktuelle Zeit "' + today.format('DD.MM.YYYY HH:mm') + ' Uhr" liegt nach dem Terminende am "' + endDateTime.format('DD.MM.YYYY HH:mm') + ' Uhr").'
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

function validateSlotTime(data) {
    let errorList = []
    
    const startHour = parseInt(data.startTime.split(':')[0])
    const endHour = parseInt(data.endTime.split(':')[0])
    const startMinute = parseInt(data.startTime.split(':')[1])
    const endMinute = parseInt(data.endTime.split(':')[1])
    
    const totalMinutes = ((endHour - startHour) * 60) + (endMinute - startMinute)
    const slotTime = parseInt(data.slotTimeInMinutes)
    
    if (totalMinutes % slotTime > 0) {
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

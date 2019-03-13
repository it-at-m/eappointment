/* global alert */

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
    const startTimestamp = startTime.set({ h: startHour, m: startMinute }).unix();
    const endTimestamp = endTime.set({ h: endHour, m: endMinute }).unix();
    const slotTime = data.slotTimeInMinutes

    if (!data.type) {
        errors.type = 'Typ erforderlich'
    }

    if (startTimestamp < todayTime && endTimestamp < todayTime) {
        errors.startTime = 'Öffnungszeiten in der Vergangenheit lassen sich nicht bearbeiten'
    }

    if (startTimestamp >= endTimestamp) {
        errors.endTime = 'Das Terminende muss nach dem Terminanfang sein'
    }

    if ((endTimestamp - startTimestamp) / 60 % slotTime > 0) {
        errors.type = 'Zeitschlitze müssen sich gleichmäßig in der Öffnungszeit aufteilen lassen'
    }

    if (selected.unix() > todayTime && startTime.startOf('day').isAfter(selected.startOf('day'), 'day')) {
        errors.startTimeFuture = `Beginn der Öffnungszeit muss vor dem ${tomorrow.format('DD.MM.YYYY')} liegen`
    }

    if (selected.unix() > todayTime && endTime.startOf('day').isBefore(selected.startOf('day'), 'day')) {
        errors.endTimeFuture = `Ende der Öffnungszeit muss nach dem ${yesterday.format('DD.MM.YYYY')} liegen`
    }

    let valid = (0 < Object.keys(errors).length) ? false : true
    return {
        valid,
        errors
    }
}

export default validate

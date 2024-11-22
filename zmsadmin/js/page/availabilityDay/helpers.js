import moment from 'moment'

export const getStateFromProps = props => {
    return {
        availabilitylistslices: writeSlotCalculationIntoAvailability(
            props.availabilitylist,
            props.maxslots,
            props.busyslots
        ),
        availabilitylist: props.availabilitylist,
        conflicts: props.conflicts,
        today: props.today,
        busyslots: props.busyslots,
        slotbuckets: props.slotbuckets,
    }
}

export const writeSlotCalculationIntoAvailability = (availabilitylist, maxslots, busyslots) => {
    return availabilitylist.map(item => {
        let itemId = item.id ? item.id : item.tempId;
        return Object.assign({}, item, {
            maxSlots: maxslots[itemId] || 0,
            busySlots: busyslots[itemId] || 0
        })
    })
}

export const mergeAvailabilityListIntoState = (state, list) => list.reduce(updateAvailabilityInState, state)

/**
 * Compare two availabilityList if they are the same using ID
 */
const equalIds = (a, b) => {
    return (a.id && b.id && a.id === b.id) || (a.tempId && b.tempId && a.tempId === b.tempId)
}

export const findAvailabilityInStateByKind = (state, kind) => {
    return state.availabilitylist.find(availabilty => availabilty.kind == kind);
}

export const updateAvailabilityInState = (state, newAvailability) => {
    let updated = false

    const newState = Object.assign({}, state, {
        availabilitylist: state.availabilitylist.map(availability => {
            if (equalIds(availability, newAvailability)) {
                updated = true
                return newAvailability
            } else {
                return availability
            }
        })
    })

    if (!updated) {
        newState.availabilitylist.push(newAvailability)
    }

    newState.stateChanged = true

    return newState
}

export const deleteAvailabilityInState = (state, deleteAvailability) => {
    return Object.assign({}, state, {
        stateChanged: true,
        availabilitylist: state.availabilitylist.filter(availabilty => availabilty.id !== deleteAvailability.id)
    })
}

export const formatTimestampDate = timestamp => moment(timestamp, 'X').format('YYYY-MM-DD')

export const getInitialState = (props) => Object.assign({}, {
    availabilitylist: [],
    selectedAvailability: null,
    formTitle: null,
    lastSave: null,
    stateChanged: false,
    selectedTab: 'table',
}, getStateFromProps(props))

export const getNewAvailability = (timestamp, tempId, scope) => {
    const now = moment(timestamp, 'X')
    const weekday = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday'
    ][now.isoWeekday() - 1]

    console.log(scope)

    const newAvailability = {
        id: null,
        tempId,
        scope: Object.assign({}, scope),
        description: 'Neue Öffnungszeit',
        startDate: timestamp,
        endDate: timestamp,
        startTime: '07:00:00',
        endTime: '20:00:00',
        bookable: {
            startInDays: "0",
            endInDays: "0"
        },
        multipleSlotsAllowed: 1,
        slotTimeInMinutes: scope.provider.data['slotTimeInMinutes'],
        weekday: {
            [weekday]: 1
        },
        workstationCount: {
            intern: 0,
            callcenter: 0,
            'public': 0
        },
        repeat: {
            afterWeeks: 0,
            weekOfMonth: 0
        },
        type: null,
        kind: "new"
    }

    return newAvailability
}

export const availabilityTypes = [
    { value: "openinghours", name: "Spontankunden" },
    { value: "appointment", name: "Terminkunden" },
]

export const weekDayList = [
    { value: "monday", label: "Montag" },
    { value: "tuesday", label: "Dienstag" },
    { value: "wednesday", label: "Mittwoch" },
    { value: "thursday", label: "Donnerstag" },
    { value: "friday", label: "Freitag" },
    { value: "saturday", label: "Samstag" },
    { value: "sunday", label: "Sonntag" }
]

export const availabilitySeries = [
    { value: "0", name: "einmaliger Termin" },
    { value: "-1", name: "jede Woche" },
    { value: "-2", name: "alle 2 Wochen" },
    { value: "-3", name: "alle 3 Wochen" },
    { value: "1", name: "jede 1. Woche im Monat" },
    { value: "2", name: "jede 2. Woche im Monat" },
    { value: "3", name: "jede 3. Woche im Monat" },
    { value: "4", name: "jede 4. Woche im Monat" },
    { value: "5", name: "jede letzte Woche im Monat" }
]

export const repeat = repeat => {
    if (repeat.afterWeeks > 0) {
        return -repeat.afterWeeks
    } else if (repeat.weekOfMonth > 0) {
        return repeat.weekOfMonth
    } else {
        return 0
    }
}

export const filterEmptyAvailability = (availability) => {
    return availability.startDate != null && availability.endDate != null
}

export const cleanupAvailabilityForSave = availability => {
    const newAvailability = Object.assign({}, availability)

    if (newAvailability.busySlots) {
        delete newAvailability.busySlots;
    }

    if (newAvailability.maxSlots) {
        delete newAvailability.maxSlots;
    }

    if (newAvailability.__modified) {
        delete newAvailability.__modified;
    }

    if (newAvailability.tempId) {
        delete newAvailability.tempId;
    }

    return newAvailability;
}

export const getDataValuesFromForm = (form, scope) => {
    return Object.assign({}, getFirstLevelValues(form), {
        bookable: {
            startInDays: form.open_from === "" ? scope.preferences.appointment.startInDaysDefault : form.open_from,
            endInDays: form.open_to === "" ? scope.preferences.appointment.endInDaysDefault : form.open_to
        },
        workstationCount: {
            intern: form.workstationCount_intern,
            callcenter: form.workstationCount_callcenter,
            "public": form.workstationCount_public
        },
        weekday: form.weekday.reduce((carry, current) => {
            return Object.assign({}, carry, { [current]: 1 })
        }, {}),
        repeat: {
            weekOfMonth: form.repeat > 0 ? form.repeat : 0,
            afterWeeks: form.repeat < 0 ? -form.repeat : 0
        }
    })
}

export const cleanupFormData = data => {
    let internCount = parseInt(data.workstationCount_intern, 10);
    let callcenterCount = parseInt(data.workstationCount_callcenter, 10);
    callcenterCount = (callcenterCount > internCount) ? internCount : callcenterCount;
    let publicCount = parseInt(data.workstationCount_public, 10);
    publicCount = (publicCount > internCount) ? internCount : publicCount;
    return Object.assign({}, data, {
        workstationCount_callcenter: callcenterCount,
        workstationCount_public: publicCount,
        open_from: (data.open_from === data.openFromDefault) ? "" : data.open_from,
        open_to: (data.open_to === data.openToDefault) ? "" : data.open_to
    })
}

export const getFirstLevelValues = data => {
    const {
        __modified,
        scope,
        description,
        startTime,
        endTime,
        startDate,
        endDate,
        multipleSlotsAllowed,
        id,
        tempId,
        type,
        slotTimeInMinutes,
        kind
    } = data

    return {
        __modified,
        scope,
        description,
        startTime,
        endTime,
        startDate,
        endDate,
        multipleSlotsAllowed,
        id,
        tempId,
        type,
        slotTimeInMinutes,
        kind
    }
}

export const getFormValuesFromData = data => {
    const workstations = Object.assign({}, data.workstationCount)

    if (parseInt(workstations.callcenter, 10) > parseInt(workstations.intern, 10)) {
        workstations.callcenter = workstations.intern
    }

    if (parseInt(workstations.public, 10) > parseInt(workstations.intern, 10)) {
        workstations.public = workstations.intern
    }

    const openFrom = data.bookable.startInDays
    const openFromDefault = data.scope.preferences.appointment.startInDaysDefault
    const openTo = data.bookable.endInDays
    const openToDefault = data.scope.preferences.appointment.endInDaysDefault
    const repeatSeries = repeat(data.repeat);

    return cleanupFormData(Object.assign({}, getFirstLevelValues(data), {
        open_from: openFrom,
        open_to: openTo,
        openFromDefault,
        openToDefault,
        repeat: repeatSeries,
        workstationCount_intern: workstations.intern,
        workstationCount_callcenter: workstations.callcenter,
        workstationCount_public: workstations.public,
        weekday: Object.keys(data.weekday).filter(key => parseInt(data.weekday[key], 10) > 0)
    }))
}

export const accordionTitle = (data) => {
    const startDate = moment(data.startDate, 'X').format('DD.MM.YYYY');
    const endDate = moment(data.endDate, 'X').format('DD.MM.YYYY');
    const startTime = moment(data.startTime, 'h:mm:ss').format('HH:mm');
    const endTime = moment(data.endTime, 'h:mm:ss').format('HH:mm');
    const availabilityType = availabilityTypes.find(element => element.value == data.type);
    const availabilityWeekDayList = Object.keys(data.weekday).filter(key => parseInt(data.weekday[key], 10) > 0)
    const availabilityWeekDay = weekDayList.filter(element => availabilityWeekDayList.includes(element.value)
    ).map(item => item.label).join(', ')
    let description = (data.description) ? `: ${data.description}` : "";
    let type = (availabilityType && availabilityWeekDay) ? ` Typ: ${availabilityType.name}, Wochentag: ${availabilityWeekDay}` : "";
    return `Zeitraum: ${startDate} bis ${endDate}, Uhrzeit: von ${startTime} bis ${endTime}, ${type}${description}`

}
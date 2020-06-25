import moment from 'moment'

export const getStateFromProps = props => {
    return {
        availabilitylistslices: props.availabilitylistslices.map(item => {
            return Object.assign({}, item, {
                maxSlots: props.maxslots[item.id] || 0,
                busySlots: props.busyslots[item.id] || 0
            })
        }),
        availabilitylist: props.availabilitylist,
        conflicts: props.conflicts,
        today: props.today    }
}

export const mergeAvailabilityListIntoState = (state, list) => list.reduce(updateAvailabilityInState, state)

/**
 * Compare two availabilities if they are the same using ID
 */
const equalIds = (a, b) => {
    return (a.id && b.id && a.id === b.id) || (a.tempId && b.tempId && a.tempId === b.tempId)
}

export const updateAvailabilityInState = (state, newAvailability) => {
    let updated = false

    const newState = Object.assign({}, state, {
        availabilitylist: state.availabilitylist.map(availabilty => {
            if (equalIds(availabilty, newAvailability)) {
                updated = true
                return newAvailability
            } else {
                return availabilty
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

export const getInitialState = (props) => Object.assign({}, {
    availabilitylist: [],
    selectedAvailability: null,
    formTitle: null,
    lastSave: null,
    stateChanged: false,
    selectedTab: 'graph'
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

    const newAvailability = {
        id: null,
        tempId,
        scope: Object.assign({}, scope),
        description: '',
        startDate: timestamp,
        endDate: timestamp,
        startTime: '00:00:00',
        endTime: '00:00:00',
        bookable: {
            startInDays: "0",
            endInDays: "0"
        },
        multipleSlotAllowed: 1,
        slotTimeInMinutes: 10,
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
        type: null
    }

    return newAvailability
}

export const availabilityTypes = [
    { value: "0", name: "--Bitte wählen--" },
    { value: "openinghours", name: "Spontankunden" },
    { value: "appointment", name: "Terminkunden" },
]

export const weekDayList=[
    { value: "monday", label: "Mo" },
    { value: "tuesday", label: "Di" },
    { value: "wednesday", label: "Mi" },
    { value: "thursday", label: "Do" },
    { value: "friday", label: "Fr" },
    { value: "saturday", label: "Sa" },
    { value: "sunday", label: "So" }
]

export const availabilitySeries=[
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

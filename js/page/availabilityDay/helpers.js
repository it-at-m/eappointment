
export const getStateFromProps = props => {
    return {
        availabilitylist: props.availabilitylist.map(item => {
            return Object.assign({}, item, {
                maxSlots: props.maxslots[item.id] || 0,
                busySlots: props.busyslots[item.id] || 0
            })
        })
    }
}

export const mergeAvailabilityListIntoState = (state, list) => list.reduce(updateAvailabilityInState, state)

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
    stateChanged: false
}, getStateFromProps(props))

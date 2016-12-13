import moment from 'moment'

export const timeToFloat = (time) => {
    const momentTime = moment(time, 'HH:mm:ss')

    return momentTime.hours() + (momentTime.minutes() / 60)
}

export const range = (start, end, step = 1) => {
    const result = []
    for (let i = start; i <= end; i += step) {
        result.push(i)
    }

    return result
}

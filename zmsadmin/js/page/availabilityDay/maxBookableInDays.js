export const DEFAULT_MAX_BOOKABLE_IN_DAYS = 365

export function getMaxBookableInDays() {
    const value = Number(document.querySelector('.availabilityDayRoot')?.dataset?.maxbookableindays)
    return Number.isFinite(value) && value > 0 ? value : DEFAULT_MAX_BOOKABLE_IN_DAYS
}

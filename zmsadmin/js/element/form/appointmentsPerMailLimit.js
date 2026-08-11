const DEFAULT_MIN = 2

export default function appointmentsPerMailLimit(root = document) {
    const checkbox = root.querySelector('#appointmentsPerMailUnlimited')
    const input = root.querySelector('#appointmentsPerMailInput')
    const hidden = root.querySelector('#appointmentsPerMailHidden')

    if (!checkbox || !input || !hidden) {
        return
    }

    let lastLimitedValue = input.value !== '' && Number(input.value) >= DEFAULT_MIN
        ? input.value
        : String(DEFAULT_MIN)

    const sync = () => {
        const unlimited = checkbox.checked

        if (unlimited) {
            if (input.value !== '' && Number(input.value) >= DEFAULT_MIN) {
                lastLimitedValue = input.value
            }
            input.value = ''
            input.disabled = true
            hidden.disabled = false
            hidden.value = ''
            return
        }

        input.disabled = false
        hidden.disabled = true
        if (input.value === '' || Number(input.value) < DEFAULT_MIN) {
            input.value = lastLimitedValue
        }
    }

    checkbox.addEventListener('change', sync)
    sync()
}

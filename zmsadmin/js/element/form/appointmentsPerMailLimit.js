const DEFAULT_MIN = 2

export default function appointmentsPerMailLimit(root = document) {
    const checkbox = root.querySelector('#appointmentsPerMailUnlimited')
    const input = root.querySelector('#appointmentsPerMailInput')
    const hidden = root.querySelector('#appointmentsPerMailHidden')

    if (!checkbox || !input || !hidden) {
        return
    }

    const sync = () => {
        const unlimited = checkbox.checked
        input.disabled = unlimited
        hidden.disabled = !unlimited

        if (unlimited) {
            hidden.value = ''
            if (input.value === '' || Number(input.value) < DEFAULT_MIN) {
                input.value = String(DEFAULT_MIN)
            }
            return
        }

        if (input.value === '' || Number(input.value) < DEFAULT_MIN) {
            input.value = String(DEFAULT_MIN)
        }
    }

    checkbox.addEventListener('change', sync)
    sync()
}

import { mountScopeMenu } from './scopeMenu'

/**
 * Initializes reusable multi-select controls for selecting or deselecting all scopes.
 */
const scopeMultiselect = (element) => {
    if (!element) {
        return
    }

    const select = element.querySelector(
        '[data-scope-multiselect-select]'
    )

    const toggleButton = element.querySelector(
        '[data-scope-multiselect-toggle]'
    )

    if (!select || !toggleButton) {
        return
    }

    if (element.hasAttribute('data-scope-menu')) {
        mountScopeMenu(select, { multiple: true })
    }

    toggleButton.addEventListener('click', () => {
        const options = Array.from(select.options)
        if (!options.length) {
            return
        }
        const allSelected = options.every((option) => option.selected)
        options.forEach((option) => {
            option.selected = !allSelected
        })
        select.dispatchEvent(new Event('change', { bubbles: true }))
    })
}

export default scopeMultiselect

const readEntries = (select) => {
    const entries = []
    Array.from(select.children).forEach((node) => {
        if (node.tagName === 'OPTGROUP') {
            Array.from(node.children).forEach((option) => {
                if (!option.value) {
                    return
                }
                entries.push({
                    option,
                    value: option.value,
                    name: (option.textContent || '').trim(),
                    group: node.getAttribute('label') || ''
                })
            })
            return
        }
        if (node.value) {
            entries.push({
                option: node,
                value: node.value,
                name: (node.textContent || '').trim(),
                group: ''
            })
        }
    })
    return entries
}

const selectedEntries = (select) => readEntries(select).filter((entry) => entry.option.selected)

const closedLabel = (select, multiple) => {
    const selected = selectedEntries(select)
    if (selected.length === 1) {
        return `(Id: ${selected[0].value}) ${selected[0].name}`
    }
    if (selected.length > 1) {
        return `${selected.length} Standorte ausgewählt`
    }
    if (!multiple) {
        const placeholder = select.querySelector('option[value=""]')
        if (placeholder) {
            return (placeholder.textContent || '').trim()
        }
    }
    return 'Standort auswählen'
}

const renderList = (select, list, query, multiple, onChoose) => {
    const needle = query.trim().toLowerCase()
    list.textContent = ''
    let lastGroup = null
    let visible = 0
    readEntries(select).forEach((entry) => {
        const haystack = `${entry.name} ${entry.value}`.toLowerCase()
        if (needle && haystack.indexOf(needle) === -1) {
            return
        }
        if (entry.group !== lastGroup) {
            const heading = document.createElement('div')
            heading.className = 'scope-combobox__group'
            heading.textContent = entry.group
            list.appendChild(heading)
            lastGroup = entry.group
        }
        const button = document.createElement('button')
        button.type = 'button'
        button.className = 'scope-combobox__option'
        button.setAttribute('role', 'option')
        button.setAttribute('aria-selected', entry.option.selected ? 'true' : 'false')
        const idCell = document.createElement('span')
        idCell.textContent = `(Id: ${entry.value})`
        const nameCell = document.createElement('span')
        nameCell.textContent = entry.name
        button.appendChild(idCell)
        button.appendChild(nameCell)
        button.addEventListener('click', () => onChoose(entry))
        list.appendChild(button)
        visible += 1
    })
    if (!visible) {
        const empty = document.createElement('div')
        empty.className = 'scope-combobox__empty'
        empty.textContent = 'Kein Standort gefunden'
        list.appendChild(empty)
    }
}

export const mountScopeMenu = (select, { multiple = false } = {}) => {
    if (!select || select.closest('.scope-combobox')) {
        return
    }

    const root = document.createElement('div')
    root.className = 'scope-combobox'
    select.parentNode.insertBefore(root, select)
    root.appendChild(select)
    select.classList.add('scope-combobox__native')
    select.tabIndex = -1

    const toggle = document.createElement('button')
    toggle.type = 'button'
    toggle.className = 'form-control scope-combobox__toggle'
    toggle.setAttribute('aria-haspopup', 'listbox')
    toggle.setAttribute('aria-expanded', 'false')
    if (select.id) {
        const label = document.querySelector(`label[for="${select.id}"]`)
        toggle.id = `${select.id}-menu`
        if (label) {
            label.htmlFor = toggle.id
        }
    }

    const panel = document.createElement('div')
    panel.className = 'scope-combobox__panel'
    panel.hidden = true

    const search = document.createElement('input')
    search.type = 'search'
    search.className = 'form-control scope-combobox__search'
    search.placeholder = 'Name oder Id'
    search.autocomplete = 'off'
    search.setAttribute('aria-label', 'Standort suchen')

    const list = document.createElement('div')
    list.className = 'scope-combobox__list'
    list.setAttribute('role', 'listbox')
    if (multiple) {
        list.setAttribute('aria-multiselectable', 'true')
    }

    panel.appendChild(search)
    panel.appendChild(list)
    root.appendChild(toggle)
    root.appendChild(panel)

    let open = false

    const paintToggle = () => {
        toggle.textContent = closedLabel(select, multiple)
    }

    const close = () => {
        open = false
        panel.hidden = true
        toggle.setAttribute('aria-expanded', 'false')
        search.value = ''
    }

    const render = () => {
        renderList(select, list, search.value, multiple, (entry) => {
            if (multiple) {
                entry.option.selected = !entry.option.selected
            } else {
                Array.from(select.options).forEach((option) => {
                    option.selected = option === entry.option
                })
                select.value = entry.value
                close()
            }
            select.dispatchEvent(new Event('change', { bubbles: true }))
        })
    }

    const openMenu = () => {
        open = true
        panel.hidden = false
        toggle.setAttribute('aria-expanded', 'true')
        render()
        search.focus()
    }

    toggle.addEventListener('click', () => {
        if (open) {
            close()
        } else {
            openMenu()
        }
    })
    search.addEventListener('input', render)
    search.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault()
        }
        if (event.key === 'Escape') {
            event.preventDefault()
            close()
            toggle.focus()
        }
    })
    document.addEventListener('click', (event) => {
        if (open && !root.contains(event.target)) {
            close()
        }
    })
    select.addEventListener('change', () => {
        paintToggle()
        if (open) {
            render()
        }
    })

    paintToggle()
}

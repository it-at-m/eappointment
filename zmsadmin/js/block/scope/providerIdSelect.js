import React, { useEffect, useRef, useState } from 'react'

const findOption = (groups, value) => {
    for (const group of groups) {
        const match = (group.options || []).find(option => String(option.value) === String(value))
        if (match) {
            return match
        }
    }
    return null
}

const closedLabel = (option) => {
    if (!option || !option.value || option.value === '0') {
        return option ? option.name : ''
    }
    return `(Id: ${option.value}) ${option.name}`
}

const isPlaceholder = (option) => !option.value || option.value === '0' || option.value === 0

const optionMatches = (option, query) => {
    if (!query) {
        return true
    }
    if (isPlaceholder(option)) {
        return false
    }
    const id = String(option.value).toLowerCase()
    const name = String(option.name || '').toLowerCase()
    return id.indexOf(query) !== -1 || name.indexOf(query) !== -1
}

const filterGroups = (groups, query) => groups
    .map(group => ({
        ...group,
        options: (group.options || []).filter(option => optionMatches(option, query))
    }))
    .filter(group => group.options.length > 0)

const realOptions = (groups) => groups.flatMap(group => (group.options || []).filter(option => !isPlaceholder(option)))

const useDismiss = (open, rootRef, onClose) => {
    useEffect(() => {
        if (!open) {
            return undefined
        }
        const onPointerDown = (event) => {
            if (rootRef.current && !rootRef.current.contains(event.target)) {
                onClose()
            }
        }
        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                onClose()
            }
        }
        document.addEventListener('mousedown', onPointerDown)
        document.addEventListener('keydown', onKeyDown)
        return () => {
            document.removeEventListener('mousedown', onPointerDown)
            document.removeEventListener('keydown', onKeyDown)
        }
    }, [open, rootRef, onClose])
}

const ProviderIdSelect = ({ groups = [], value, name, disabled, onChange }) => {
    const [open, setOpen] = useState(false)
    const [query, setQuery] = useState('')
    const rootRef = useRef(null)
    const searchRef = useRef(null)
    const selected = findOption(groups, value)

    const close = () => {
        setOpen(false)
        setQuery('')
    }

    useDismiss(open, rootRef, close)

    useEffect(() => {
        if (open && searchRef.current) {
            searchRef.current.focus()
        }
    }, [open])

    const choose = (optionValue) => {
        onChange(name, optionValue)
        close()
    }

    const toggle = () => {
        if (disabled) {
            return
        }
        if (open) {
            close()
        } else {
            setOpen(true)
        }
    }

    return (
        <div ref={rootRef} style={{ position: 'relative' }}>
            <input type="hidden" name={name} value={value ?? ''} disabled={disabled} />
            <button
                type="button"
                id="scopeProviderId"
                className="form-control"
                disabled={disabled}
                aria-haspopup="listbox"
                aria-expanded={open}
                onClick={toggle}
                style={{ textAlign: 'left' }}
            >
                {closedLabel(selected)}
            </button>
            {open && (
                <ProviderMenu
                    groups={filterGroups(groups, query.trim().toLowerCase())}
                    query={query}
                    value={value}
                    searchRef={searchRef}
                    onQuery={setQuery}
                    onChoose={choose}
                />
            )}
        </div>
    )
}

const ProviderMenu = ({ groups, query, value, searchRef, onQuery, onChoose }) => {
    const onSearchKeyDown = (event) => {
        if (event.key !== 'Enter') {
            return
        }
        event.preventDefault()
        const trimmed = query.trim()
        if (!trimmed) {
            return
        }
        const matches = realOptions(groups)
        if (matches.length === 1) {
            onChoose(matches[0].value)
        }
    }

    return (
        <>
            <style>{`
                .provider-id-menu-option { background: transparent; color: inherit; }
                .provider-id-menu-option:hover { background: #e8f1fb; }
                .provider-id-menu-option[aria-selected="true"],
                .provider-id-menu-option[aria-selected="true"]:hover { background: #0076dd; color: #fff; }
            `}</style>
            <div
                style={{
                    position: 'absolute',
                    zIndex: 30,
                    left: 0,
                    top: '100%',
                    minWidth: '100%',
                    width: 'max-content',
                    maxWidth: '90vw',
                    background: '#fff',
                    border: '1px solid #ccc',
                    boxShadow: '0 4px 16px rgba(0, 0, 0, 0.15)'
                }}
            >
                <input
                    ref={searchRef}
                    type="search"
                    className="form-control"
                    placeholder="Name oder Id"
                    aria-label="Anbieter suchen"
                    autoComplete="off"
                    value={query}
                    onChange={(event) => onQuery(event.target.value)}
                    onKeyDown={onSearchKeyDown}
                    style={{ width: 'calc(100% - 16px)', margin: '8px' }}
                />
                <div
                    role="listbox"
                    aria-labelledby="scopeProviderId"
                    style={{
                        maxHeight: '20rem',
                        overflowY: 'auto',
                        display: 'grid',
                        gridTemplateColumns: 'max-content minmax(12rem, auto)',
                        columnGap: '1.25rem',
                        padding: '0.25rem 0'
                    }}
                >
                    {groups.map((group) => (
                        <ProviderGroup
                            key={group.label || 'options'}
                            group={group}
                            value={value}
                            onChoose={onChoose}
                        />
                    ))}
                    {realOptions(groups).length === 0 && (
                        <div style={{ gridColumn: '1 / -1', padding: '0.35rem 0.75rem', color: '#555' }}>
                            Kein Anbieter gefunden
                        </div>
                    )}
                </div>
            </div>
        </>
    )
}

const ProviderGroup = ({ group, value, onChoose }) => {
    return (
        <>
            {group.label && (
                <div style={{ gridColumn: '1 / -1', padding: '0.35rem 0.75rem 0.15rem', color: '#666', fontSize: '0.85em' }}>
                    {group.label}
                </div>
            )}
            {(group.options || []).map((option) => (
                <ProviderOption
                    key={`${group.label}-${option.value}`}
                    option={option}
                    selected={String(option.value) === String(value)}
                    onChoose={onChoose}
                />
            ))}
        </>
    )
}

const ProviderOption = ({ option, selected, onChoose }) => {
    const hasId = option.value && option.value !== '0'
    return (
        <button
            type="button"
            role="option"
            className="provider-id-menu-option"
            aria-selected={selected}
            onClick={() => onChoose(option.value)}
            style={{
                display: 'grid',
                gridTemplateColumns: 'subgrid',
                gridColumn: '1 / -1',
                alignItems: 'baseline',
                width: '100%',
                border: 0,
                textAlign: 'left',
                padding: '0.2rem 0.75rem',
                font: 'inherit',
                cursor: 'pointer'
            }}
        >
            {hasId ? (
                <>
                    <span style={{ fontVariantNumeric: 'tabular-nums', whiteSpace: 'nowrap' }}>(Id: {option.value})</span>
                    <span>{option.name}</span>
                </>
            ) : (
                <span style={{ gridColumn: '1 / -1' }}>{option.name}</span>
            )}
        </button>
    )
}

export default ProviderIdSelect

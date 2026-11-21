export default function searchScopePicker(picker) {
    const summary = picker.querySelector('summary');
    const scopeValue = picker.querySelector('#search-scope-value');

    const updateScopeValue = () => {
        const count = picker.querySelectorAll('input[type="checkbox"]:checked').length;
        scopeValue.textContent = count === 0 ? scopeValue.dataset.emptyLabel : `${count} ausgewählt`;
    };

    picker.addEventListener('change', updateScopeValue);

    picker.addEventListener('click', (event) => {
        const checkbox = event.target.closest('label.search-scope-option')?.control;
        if (!checkbox || checkbox.disabled || event.target === checkbox) {
            return;
        }

        // Toggle the checkbox directly to avoid the browser forwarding a second click.
        event.preventDefault();
        checkbox.checked = !checkbox.checked;
        updateScopeValue();
    });

    picker.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && picker.open) {
            event.preventDefault();
            picker.open = false;
            summary.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (picker.open && !picker.contains(event.target)) {
            picker.open = false;
        }
    });

    updateScopeValue();
}

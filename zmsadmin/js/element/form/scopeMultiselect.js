/**
 * Initializes reusable multi-select controls for selecting or deselecting all scopes.
 */
const scopeMultiselect = (element) => {
    if (!element) {
        return;
    }

    const select = element.querySelector(
        '[data-scope-multiselect-select]'
    );

    const toggleButton = element.querySelector(
        '[data-scope-multiselect-toggle]'
    );

    if (!select || !toggleButton) {
        return;
    }

    toggleButton.addEventListener('click', () => {
        const options = Array.from(select.options);

        const allSelected = options.every(
            (option) => option.selected
        );

        options.forEach((option) => {
            option.selected = !allSelected;
        });
    });
};

export default scopeMultiselect;

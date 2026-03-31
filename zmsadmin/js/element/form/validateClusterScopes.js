/**
 * Validates that at least one scope is selected before submitting a cluster form
 */
const validateClusterScopes = (element) => {
    const form = element;
    if (!form) return;

    const errorMessage = form.querySelector('#scope-validation-error');
    if (!errorMessage) return;

    // Find all checkboxes and filter for scope ones
    const allCheckboxes = form.querySelectorAll('input[type="checkbox"]');
    const scopeCheckboxes = Array.from(allCheckboxes).filter(function(cb) {
        return cb.name && cb.name.indexOf('scopes[') === 0 && !cb.disabled;
    });

    // If there are scope checkboxes, add change listeners
    if (scopeCheckboxes.length > 0) {
        scopeCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                if (this.checked && errorMessage) {
                    errorMessage.style.display = 'none';
                }
            });
        });
    }

    form.addEventListener('submit', function(e) {
        // If there are no scope checkboxes at all, prevent submission
        if (scopeCheckboxes.length === 0) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (errorMessage) {
                errorMessage.style.display = 'block';
                errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            return false;
        }

        // Check if at least one scope is selected
        let hasCheckedScope = false;
        scopeCheckboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                hasCheckedScope = true;
            }
        });

        if (!hasCheckedScope) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (errorMessage) {
                errorMessage.style.display = 'block';
                errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            return false;
        } else if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    });
}

export default validateClusterScopes


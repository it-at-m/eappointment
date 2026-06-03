package de.muenchen.zms.citizen.thinnedprocess.validation;

import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;

public interface ThinnedProcessValidator {
    void validate(ThinnedProcessView view) throws ThinnedProcessValidationException;
}

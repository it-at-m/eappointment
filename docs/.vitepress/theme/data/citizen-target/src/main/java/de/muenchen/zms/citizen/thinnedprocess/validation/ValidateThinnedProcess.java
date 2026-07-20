package de.muenchen.zms.citizen.thinnedprocess.validation;

import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.stereotype.Component;

/**
 * Validates {@link ThinnedProcessView} before write operations.
 * today: citizenapi/thinnedProcess.json rules via ValidationService
 */
@Component
public class ValidateThinnedProcess implements ThinnedProcessValidator {

    @Override
    public void validate(ThinnedProcessView view) throws ThinnedProcessValidationException {
        if (view == null) {
            throw new ThinnedProcessValidationException("Process payload cannot be null.");
        }
        if (view.officeId() == null || view.officeId() <= 0) {
            throw new ThinnedProcessValidationException("officeId is required.");
        }
        if (view.timestamp() == null || view.timestamp().isBlank()) {
            throw new ThinnedProcessValidationException("timestamp is required for reservation.");
        }
    }
}

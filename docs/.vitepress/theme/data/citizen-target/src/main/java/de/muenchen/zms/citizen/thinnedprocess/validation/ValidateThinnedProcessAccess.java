package de.muenchen.zms.citizen.thinnedprocess.validation;

import de.muenchen.zms.citizen.thinnedprocess.exception.ThinnedProcessAccessDeniedException;
import org.springframework.stereotype.Component;

/**
 * Validates processId + authKey query/body parameters before load.
 * today: zmscitizenapi\\Services\\Core\\ValidationService::validateGetProcessById
 */
@Component
public class ValidateThinnedProcessAccess {

    public void validateGetById(Long processId, String authKey, boolean authenticated) {
        if (processId == null || processId <= 0) {
            throw new ThinnedProcessValidationException("processId must be a positive number.");
        }
        if (!authenticated && (authKey == null || authKey.isBlank())) {
            throw new ThinnedProcessAccessDeniedException("authKey is required for unauthenticated access.");
        }
    }

    public void validateMutatingAccess(Long processId, String authKey, boolean authenticated) {
        validateGetById(processId, authKey, authenticated);
    }
}

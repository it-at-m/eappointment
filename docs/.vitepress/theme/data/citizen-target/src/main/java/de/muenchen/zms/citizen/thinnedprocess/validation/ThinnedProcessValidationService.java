package de.muenchen.zms.citizen.thinnedprocess.validation;

import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.stereotype.Component;

/** today: zmscitizenapi\\Services\\Core\\ValidationService (appointment rules) */
@Component
public class ThinnedProcessValidationService {

    private final ThinnedProcessValidator processValidator;
    private final ValidateThinnedProcessAccess accessValidator;

    ThinnedProcessValidationService(ThinnedProcessValidator processValidator, ValidateThinnedProcessAccess accessValidator) {
        this.processValidator = processValidator;
        this.accessValidator = accessValidator;
    }

    public void validateForReserve(ThinnedProcessView input) {
        processValidator.validate(input);
    }

    public void validateGetById(Long processId, String authKey, boolean authenticated) {
        accessValidator.validateGetById(processId, authKey, authenticated);
    }
}

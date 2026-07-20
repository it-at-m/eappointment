package de.muenchen.zms.citizen.thinnedprocess.exception;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

/** today: ValidationService::validateGetProcessNotFound → processNotFound */
@ResponseStatus(HttpStatus.NOT_FOUND)
public class ThinnedProcessNotFoundException extends RuntimeException {

    public ThinnedProcessNotFoundException(Long processId) {
        super("Process not found: " + processId);
    }
}

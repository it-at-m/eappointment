package de.muenchen.zms.citizen.thinnedprocess.validation;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

/** today: zmscitizenapi error payloads from ValidationService */
@ResponseStatus(HttpStatus.BAD_REQUEST)
public class ThinnedProcessValidationException extends RuntimeException {

    public ThinnedProcessValidationException(String message) {
        super(message);
    }
}

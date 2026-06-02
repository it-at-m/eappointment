package de.muenchen.zms.citizen.thinnedprocess.exception;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

/** today: UnauthorizedException, authKeyMismatch */
@ResponseStatus(HttpStatus.UNAUTHORIZED)
public class ThinnedProcessAccessDeniedException extends RuntimeException {

    public ThinnedProcessAccessDeniedException(String message) {
        super(message);
    }
}

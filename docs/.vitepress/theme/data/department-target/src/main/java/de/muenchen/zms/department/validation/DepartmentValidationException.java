package de.muenchen.zms.department.validation;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(HttpStatus.BAD_REQUEST)
public class DepartmentValidationException extends RuntimeException {

    public DepartmentValidationException(String message) {
        super(message);
    }
}

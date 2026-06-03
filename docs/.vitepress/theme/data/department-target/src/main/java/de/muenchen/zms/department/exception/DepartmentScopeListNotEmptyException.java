package de.muenchen.zms.department.exception;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(HttpStatus.PRECONDITION_REQUIRED)
public class DepartmentScopeListNotEmptyException extends RuntimeException {
    public DepartmentScopeListNotEmptyException(Long id) {
        super("Department " + id + " still has scopes or clusters");
    }
}

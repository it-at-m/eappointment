package de.muenchen.zms.department.validation;

import de.muenchen.zms.department.view.DepartmentView;

public interface DepartmentValidator {
    void validate(DepartmentView view) throws DepartmentValidationException;
}

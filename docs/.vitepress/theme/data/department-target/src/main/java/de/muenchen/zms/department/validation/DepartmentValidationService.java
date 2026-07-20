package de.muenchen.zms.department.validation;

import de.muenchen.zms.department.view.DepartmentView;
import org.springframework.stereotype.Component;

/** today: {@code $entity->testValid()} replaced by imperative rules on {@link DepartmentView} */
@Component
public class DepartmentValidationService {

    private final ValidateDepartment validator;

    DepartmentValidationService(ValidateDepartment validator) {
        this.validator = validator;
    }

    public void validateForWrite(DepartmentView view) {
        validator.validate(view);
    }
}

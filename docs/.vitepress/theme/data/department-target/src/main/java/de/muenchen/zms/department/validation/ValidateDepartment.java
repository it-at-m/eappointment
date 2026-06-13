package de.muenchen.zms.department.validation;

import de.muenchen.zms.department.repository.DepartmentRepository;
import de.muenchen.zms.department.view.DepartmentView;
import java.util.regex.Pattern;
import org.springframework.stereotype.Component;

/**
 * Validates {@link DepartmentView} before write operations.
 *
 * <p>RefArch pattern: {@code validation/rules/Validate*.java} with explicit {@code validate(view)}
 * calls (like {@code ValidateLinks}, {@code ValidateLanguages}). Rules from today's
 * {@code department.json} (name, email pattern, …) are expressed here in Java — not via JSON Schema.
 */
@Component
public class ValidateDepartment implements DepartmentValidator {

    private static final Pattern EMAIL =
            Pattern.compile("^[a-zA-Z0-9_\\-\\.]{2,}@[a-zA-Z0-9_\\-\\.]{2,}\\.[a-z]{2,}$|^$");

    private final DepartmentRepository repository;

    ValidateDepartment(DepartmentRepository repository) {
        this.repository = repository;
    }

    @Override
    public void validate(DepartmentView view) throws DepartmentValidationException {
        if (view == null) {
            throw new DepartmentValidationException("Department payload cannot be null.");
        }
        if (view.name() == null || view.name().isBlank()) {
            throw new DepartmentValidationException("Name cannot be null or empty.");
        }
        if (view.email() != null && !EMAIL.matcher(view.email()).matches()) {
            throw new DepartmentValidationException(
                    "Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein");
        }
        if (view.sendEmailReminderMinutesBefore() != null && view.sendEmailReminderMinutesBefore() < 0) {
            throw new DepartmentValidationException("Reminder minutes before must be a non-negative number.");
        }
        if (view.id() != null && !repository.existsById(view.id())) {
            throw new DepartmentValidationException("Department id does not exist: " + view.id());
        }
    }
}

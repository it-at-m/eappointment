package de.muenchen.zms.department.service;

import de.muenchen.zms.department.exception.DepartmentNotFoundException;
import de.muenchen.zms.department.model.Department;
import de.muenchen.zms.department.repository.DepartmentRepository;
import de.muenchen.zms.department.view.DepartmentView;
import java.util.List;
import org.springframework.stereotype.Service;

/** today: zmsbackend\\Helper\\User::checkDepartment / checkDepartments */
@Service
public class DepartmentAccessService {

    private final DepartmentRepository repository;

    DepartmentAccessService(DepartmentRepository repository) {
        this.repository = repository;
    }

    public DepartmentView requireDepartment(Long id) {
        return repository
                .findViewById(id)
                .orElseThrow(() -> new DepartmentNotFoundException(id));
    }

    public List<Long> filterAccessibleDepartmentIds(List<Long> requested) {
        // today: superuser bypass; otherwise intersect with assigned departments
        return requested.stream().filter(repository::existsById).toList();
    }
}

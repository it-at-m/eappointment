package de.muenchen.zms.department.service;

import de.muenchen.zms.department.exception.DepartmentNotFoundException;
import de.muenchen.zms.department.repository.DepartmentRepository;
import de.muenchen.zms.department.view.DepartmentView;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsapi\\DepartmentGet, zmsdb\\Department::readEntity */
@Service
public class DepartmentFetchService {

    private final DepartmentRepository repository;
    private final DepartmentReferenceLoader referenceLoader;

    DepartmentFetchService(DepartmentRepository repository, DepartmentReferenceLoader referenceLoader) {
        this.repository = repository;
        this.referenceLoader = referenceLoader;
    }

    @Transactional(readOnly = true)
    public DepartmentView getById(Long id, int resolveReferences) {
        return repository
                .findViewById(id)
                .map(view -> referenceLoader.withReferences(view, resolveReferences))
                .orElseThrow(() -> new DepartmentNotFoundException(id));
    }
}

package de.muenchen.zms.department.service;

import de.muenchen.zms.department.exception.DepartmentNotFoundException;
import de.muenchen.zms.department.repository.DepartmentRepository;
import de.muenchen.zms.department.view.DepartmentView;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsapi\\DepartmentByScopeId, zmsdb\\Department::readByScopeId */
@Service
public class DepartmentByScopeFetchService {

    private final DepartmentRepository repository;
    private final DepartmentReferenceLoader referenceLoader;

    DepartmentByScopeFetchService(
            DepartmentRepository repository, DepartmentReferenceLoader referenceLoader) {
        this.repository = repository;
        this.referenceLoader = referenceLoader;
    }

    @Transactional(readOnly = true)
    public DepartmentView getByScopeId(Long scopeId, int resolveReferences, boolean reduceForAnonymous) {
        DepartmentView view =
                repository
                        .findViewByScopeId(scopeId)
                        .map(v -> referenceLoader.withReferences(v, resolveReferences))
                        .orElseThrow(() -> new DepartmentNotFoundException(scopeId));
        return reduceForAnonymous ? view.withLessData() : view;
    }
}

package de.muenchen.zms.department.service;

import de.muenchen.zms.department.repository.DepartmentRepository;
import de.muenchen.zms.department.view.DepartmentView;
import java.util.List;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsapi\\DepartmentList, zmsdb\\Department::readList */
@Service
public class DepartmentListService {

    private final DepartmentRepository repository;
    private final DepartmentReferenceLoader referenceLoader;

    DepartmentListService(DepartmentRepository repository, DepartmentReferenceLoader referenceLoader) {
        this.repository = repository;
        this.referenceLoader = referenceLoader;
    }

    @Transactional(readOnly = true)
    public List<DepartmentView> list(int resolveReferences) {
        return repository.findAllViews().stream()
                .map(view -> referenceLoader.withReferences(view, resolveReferences))
                .toList();
    }
}

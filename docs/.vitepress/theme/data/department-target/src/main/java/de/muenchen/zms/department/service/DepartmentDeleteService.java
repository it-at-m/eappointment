package de.muenchen.zms.department.service;

import de.muenchen.zms.department.exception.DepartmentNotFoundException;
import de.muenchen.zms.department.exception.DepartmentScopeListNotEmptyException;
import de.muenchen.zms.department.repository.DepartmentClusterRepository;
import de.muenchen.zms.department.repository.DepartmentRepository;
import de.muenchen.zms.department.repository.DepartmentScopeRepository;
import de.muenchen.zms.department.view.DepartmentView;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsbackend\\Department\\Api\\DepartmentDelete, zmsbackend\\Department\\Service\\Department::deleteEntity */
@Service
public class DepartmentDeleteService {

    private final DepartmentRepository repository;
    private final DepartmentScopeRepository scopeRepository;
    private final DepartmentClusterRepository clusterRepository;
    private final DepartmentFetchService fetchService;

    DepartmentDeleteService(
            DepartmentRepository repository,
            DepartmentScopeRepository scopeRepository,
            DepartmentClusterRepository clusterRepository,
            DepartmentFetchService fetchService) {
        this.repository = repository;
        this.scopeRepository = scopeRepository;
        this.clusterRepository = clusterRepository;
        this.fetchService = fetchService;
    }

    @Transactional
    public DepartmentView delete(Long id) {
        DepartmentView department = fetchService.getById(id, 1);
        if (scopeRepository.countByDepartmentId(id) > 0
                || clusterRepository.countByDepartmentId(id) > 0) {
            throw new DepartmentScopeListNotEmptyException(id);
        }
        if (!repository.existsById(id)) {
            throw new DepartmentNotFoundException(id);
        }
        repository.deleteById(id);
        return department;
    }
}

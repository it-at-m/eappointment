package de.muenchen.zms.department.service;

import de.muenchen.zms.department.exception.DepartmentNotFoundException;
import de.muenchen.zms.department.repository.DepartmentOrganisationRepository;
import de.muenchen.zms.department.view.OrganisationView;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsbackend\\Organisation\\Api\\OrganisationByDepartment, zmsbackend\\Organisation\\Service\\Organisation::readByDepartmentId */
@Service
public class DepartmentOrganisationFetchService {

    private final DepartmentFetchService fetchService;
    private final DepartmentOrganisationRepository organisationRepository;

    DepartmentOrganisationFetchService(
            DepartmentFetchService fetchService, DepartmentOrganisationRepository organisationRepository) {
        this.fetchService = fetchService;
        this.organisationRepository = organisationRepository;
    }

    @Transactional(readOnly = true)
    public OrganisationView getOrganisation(Long departmentId, int resolveReferences) {
        fetchService.getById(departmentId, 0);
        return organisationRepository
                .findByDepartmentId(departmentId)
                .orElseThrow(() -> new DepartmentNotFoundException(departmentId));
    }
}

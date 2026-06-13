package de.muenchen.zms.department.service;

import de.muenchen.zms.department.model.Department;
import de.muenchen.zms.department.repository.DepartmentRepository;
import de.muenchen.zms.department.validation.DepartmentValidationService;
import de.muenchen.zms.department.view.DepartmentView;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsapi\\OrganisationAddDepartment, zmsdb\\Department::writeEntity */
@Service
public class DepartmentCreateService {

    private final DepartmentRepository repository;
    private final DepartmentFetchService fetchService;
    private final DepartmentWriteSupport writeSupport;
    private final DepartmentValidationService validationService;

    DepartmentCreateService(
            DepartmentRepository repository,
            DepartmentFetchService fetchService,
            DepartmentWriteSupport writeSupport,
            DepartmentValidationService validationService) {
        this.repository = repository;
        this.fetchService = fetchService;
        this.writeSupport = writeSupport;
        this.validationService = validationService;
    }

    @Transactional
    public DepartmentView createUnderOrganisation(Long organisationId, DepartmentView input) {
        validationService.validateForWrite(input);
        Department entity = new Department();
        entity.setOrganisationId(organisationId);
        entity.setOwnerId(writeSupport.resolveOwnerIdForOrganisation(organisationId));
        writeSupport.applyView(entity, input);
        Department saved = repository.save(entity);
        writeSupport.writeNestedEntities(saved.getId(), input);
        return fetchService.getById(saved.getId(), 0);
    }
}

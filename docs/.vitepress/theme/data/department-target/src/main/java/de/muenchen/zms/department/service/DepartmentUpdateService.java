package de.muenchen.zms.department.service;

import de.muenchen.zms.department.exception.DepartmentNotFoundException;
import de.muenchen.zms.department.model.Department;
import de.muenchen.zms.department.repository.DepartmentRepository;
import de.muenchen.zms.department.validation.DepartmentValidationService;
import de.muenchen.zms.department.view.DepartmentView;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsapi\\DepartmentUpdate, zmsdb\\Department::updateEntity */
@Service
public class DepartmentUpdateService {

    private final DepartmentRepository repository;
    private final DepartmentFetchService fetchService;
    private final DepartmentWriteSupport writeSupport;
    private final DepartmentValidationService validationService;

    DepartmentUpdateService(
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
    public DepartmentView update(Long id, DepartmentView input) {
        validationService.validateForWrite(input);
        Department entity =
                repository.findById(id).orElseThrow(() -> new DepartmentNotFoundException(id));
        writeSupport.applyView(entity, input);
        repository.save(entity);
        writeSupport.writeNestedEntities(id, input);
        return fetchService.getById(id, 0);
    }
}

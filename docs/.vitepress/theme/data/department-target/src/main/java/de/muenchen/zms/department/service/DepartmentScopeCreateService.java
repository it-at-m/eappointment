package de.muenchen.zms.department.service;

import de.muenchen.zms.department.view.ScopeView;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsapi\\DepartmentAddScope, zmsdb\\Scope::writeEntity */
@Service
public class DepartmentScopeCreateService {

    private final DepartmentFetchService fetchService;

    DepartmentScopeCreateService(DepartmentFetchService fetchService) {
        this.fetchService = fetchService;
    }

    @Transactional
    public ScopeView addScope(Long departmentId, ScopeView input) {
        fetchService.getById(departmentId, 1);
        // today: validate scope JSON against zmsentities/schema/scope.json, then persist
        return input;
    }
}

package de.muenchen.zms.department.api;

import de.muenchen.zms.department.service.DepartmentCreateService;
import de.muenchen.zms.department.view.DepartmentView;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/** today: POST /organisation/{id}/department/ → OrganisationAddDepartment */
@RestController
@RequestMapping("/api/v2/organisation")
public class OrganisationDepartmentController {

    private final DepartmentCreateService createService;

    OrganisationDepartmentController(DepartmentCreateService createService) {
        this.createService = createService;
    }

    @PostMapping("/{organisationId}/department")
    @PreAuthorize("hasAuthority('department')")
    public ResponseEntity<DepartmentView> addDepartment(
            @PathVariable Long organisationId, @RequestBody DepartmentView input) {
        return ResponseEntity.ok(createService.createUnderOrganisation(organisationId, input));
    }
}

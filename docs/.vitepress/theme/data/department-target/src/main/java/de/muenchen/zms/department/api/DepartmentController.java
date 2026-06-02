package de.muenchen.zms.department.api;

import de.muenchen.zms.department.service.DepartmentClusterCreateService;
import de.muenchen.zms.department.service.DepartmentDeleteService;
import de.muenchen.zms.department.service.DepartmentFetchService;
import de.muenchen.zms.department.service.DepartmentListService;
import de.muenchen.zms.department.service.DepartmentOrganisationFetchService;
import de.muenchen.zms.department.service.DepartmentScopeCreateService;
import de.muenchen.zms.department.service.DepartmentUpdateService;
import de.muenchen.zms.department.service.DepartmentWorkstationListService;
import de.muenchen.zms.department.view.ClusterView;
import de.muenchen.zms.department.view.DepartmentView;
import de.muenchen.zms.department.view.OrganisationView;
import de.muenchen.zms.department.view.ScopeView;
import de.muenchen.zms.department.view.WorkstationView;
import java.util.List;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

/**
 * REST layer for /department/* routes.
 * today: DepartmentGet, DepartmentList, DepartmentUpdate, DepartmentDelete,
 * DepartmentAddScope, DepartmentAddCluster, OrganisationByDepartment, DepartmentWorkstationList
 */
@RestController
@RequestMapping("/api/v2/department")
public class DepartmentController {

    private final DepartmentFetchService fetchService;
    private final DepartmentListService listService;
    private final DepartmentUpdateService updateService;
    private final DepartmentDeleteService deleteService;
    private final DepartmentScopeCreateService scopeCreateService;
    private final DepartmentClusterCreateService clusterCreateService;
    private final DepartmentOrganisationFetchService organisationFetchService;
    private final DepartmentWorkstationListService workstationListService;

    DepartmentController(
            DepartmentFetchService fetchService,
            DepartmentListService listService,
            DepartmentUpdateService updateService,
            DepartmentDeleteService deleteService,
            DepartmentScopeCreateService scopeCreateService,
            DepartmentClusterCreateService clusterCreateService,
            DepartmentOrganisationFetchService organisationFetchService,
            DepartmentWorkstationListService workstationListService) {
        this.fetchService = fetchService;
        this.listService = listService;
        this.updateService = updateService;
        this.deleteService = deleteService;
        this.scopeCreateService = scopeCreateService;
        this.clusterCreateService = clusterCreateService;
        this.organisationFetchService = organisationFetchService;
        this.workstationListService = workstationListService;
    }

    /** today: GET /department/ → DepartmentList */
    @GetMapping
    @PreAuthorize("hasAuthority('basic')")
    public ResponseEntity<List<DepartmentView>> listDepartments(
            @RequestParam(defaultValue = "0") int resolveReferences) {
        return ResponseEntity.ok(listService.list(resolveReferences));
    }

    /** today: GET /department/{id}/ → DepartmentGet */
    @GetMapping("/{id}")
    @PreAuthorize("hasAuthority('department')")
    public ResponseEntity<DepartmentView> getDepartment(
            @PathVariable Long id, @RequestParam(defaultValue = "1") int resolveReferences) {
        return ResponseEntity.ok(fetchService.getById(id, resolveReferences));
    }

    /** today: POST /department/{id}/ → DepartmentUpdate */
    @PutMapping("/{id}")
    @PreAuthorize("hasAuthority('department')")
    public ResponseEntity<DepartmentView> updateDepartment(
            @PathVariable Long id, @RequestBody DepartmentView input) {
        return ResponseEntity.ok(updateService.update(id, input));
    }

    /** today: DELETE /department/{id}/ → DepartmentDelete */
    @DeleteMapping("/{id}")
    @PreAuthorize("hasAuthority('department')")
    public ResponseEntity<DepartmentView> deleteDepartment(@PathVariable Long id) {
        return ResponseEntity.ok(deleteService.delete(id));
    }

    /** today: POST /department/{id}/scope/ → DepartmentAddScope */
    @PostMapping("/{id}/scope")
    @PreAuthorize("hasAuthority('department')")
    public ResponseEntity<ScopeView> addScope(@PathVariable Long id, @RequestBody ScopeView input) {
        return ResponseEntity.ok(scopeCreateService.addScope(id, input));
    }

    /** today: POST /department/{id}/cluster/ → DepartmentAddCluster */
    @PostMapping("/{id}/cluster")
    @PreAuthorize("hasAuthority('department')")
    public ResponseEntity<ClusterView> addCluster(@PathVariable Long id, @RequestBody ClusterView input) {
        return ResponseEntity.ok(clusterCreateService.addCluster(id, input));
    }

    /** today: GET /department/{id}/organisation/ → OrganisationByDepartment */
    @GetMapping("/{id}/organisation")
    @PreAuthorize("hasAuthority('department')")
    public ResponseEntity<OrganisationView> getOrganisation(
            @PathVariable Long id, @RequestParam(defaultValue = "0") int resolveReferences) {
        return ResponseEntity.ok(organisationFetchService.getOrganisation(id, resolveReferences));
    }

    /** today: GET /department/{id}/workstation/ → DepartmentWorkstationList */
    @GetMapping("/{id}/workstation")
    @PreAuthorize("hasAuthority('useraccount')")
    public ResponseEntity<List<WorkstationView>> listWorkstations(
            @PathVariable Long id, @RequestParam(defaultValue = "1") int resolveReferences) {
        return ResponseEntity.ok(workstationListService.listWorkstations(id, resolveReferences));
    }
}

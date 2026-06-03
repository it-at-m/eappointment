package de.muenchen.zms.department.api;

import de.muenchen.zms.department.service.DepartmentByScopeFetchService;
import de.muenchen.zms.department.view.DepartmentView;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

/** today: GET /scope/{id}/department/ → DepartmentByScopeId */
@RestController
@RequestMapping("/api/v2/scope")
public class ScopeDepartmentController {

    private final DepartmentByScopeFetchService byScopeFetchService;

    ScopeDepartmentController(DepartmentByScopeFetchService byScopeFetchService) {
        this.byScopeFetchService = byScopeFetchService;
    }

    @GetMapping("/{scopeId}/department")
    public ResponseEntity<DepartmentView> getDepartmentByScope(
            @PathVariable Long scopeId,
            @RequestParam(defaultValue = "1") int resolveReferences,
            Authentication authentication) {
        boolean authenticated = authentication != null && authentication.isAuthenticated();
        DepartmentView view =
                byScopeFetchService.getByScopeId(scopeId, resolveReferences, !authenticated);
        return ResponseEntity.ok(view);
    }
}

package de.muenchen.zms.department.api;

import de.muenchen.zms.department.service.DepartmentUseraccountByRoleListService;
import de.muenchen.zms.department.service.DepartmentUseraccountListService;
import de.muenchen.zms.department.view.UseraccountView;
import java.util.List;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

/**
 * today: GET /department/{ids}/useraccount/ → UseraccountListByDepartments
 * today: GET /role/{level}/department/{ids}/useraccount/ → UseraccountListByRoleAndDepartments
 */
@RestController
public class DepartmentUseraccountController {

    private final DepartmentUseraccountListService useraccountListService;
    private final DepartmentUseraccountByRoleListService useraccountByRoleListService;

    DepartmentUseraccountController(
            DepartmentUseraccountListService useraccountListService,
            DepartmentUseraccountByRoleListService useraccountByRoleListService) {
        this.useraccountListService = useraccountListService;
        this.useraccountByRoleListService = useraccountByRoleListService;
    }

    @GetMapping("/api/v2/department/{ids}/useraccount")
    @PreAuthorize("hasAuthority('useraccount')")
    public ResponseEntity<List<UseraccountView>> listUseraccountsByDepartments(
            @PathVariable String ids, @RequestParam(required = false) String query) {
        return ResponseEntity.ok(useraccountListService.listByDepartmentIds(ids, query));
    }

    @GetMapping("/api/v2/role/{level}/department/{ids}/useraccount")
    @PreAuthorize("hasAuthority('useraccount')")
    public ResponseEntity<List<UseraccountView>> listUseraccountsByRoleAndDepartments(
            @PathVariable Long level, @PathVariable String ids) {
        return ResponseEntity.ok(useraccountByRoleListService.listByRoleAndDepartmentIds(level, ids));
    }
}

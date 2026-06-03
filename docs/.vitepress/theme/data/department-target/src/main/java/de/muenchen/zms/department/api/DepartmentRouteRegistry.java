package de.muenchen.zms.department.api;

/**
 * Route map for the department vertical slice (mirrors zmsapi/routing.php excerpt).
 *
 * <pre>
 * GET    /api/v2/department/                              → DepartmentList
 * GET    /api/v2/department/{id}                          → DepartmentGet
 * PUT    /api/v2/department/{id}                          → DepartmentUpdate
 * DELETE /api/v2/department/{id}                          → DepartmentDelete
 * POST   /api/v2/department/{id}/scope                    → DepartmentAddScope
 * POST   /api/v2/department/{id}/cluster                  → DepartmentAddCluster
 * GET    /api/v2/department/{id}/organisation               → OrganisationByDepartment
 * GET    /api/v2/department/{ids}/useraccount             → UseraccountListByDepartments
 * GET    /api/v2/department/{id}/workstation              → DepartmentWorkstationList
 * POST   /api/v2/organisation/{id}/department             → OrganisationAddDepartment
 * GET    /api/v2/scope/{id}/department                    → DepartmentByScopeId
 * GET    /api/v2/role/{level}/department/{ids}/useraccount → UseraccountListByRoleAndDepartments
 * </pre>
 */
public final class DepartmentRouteRegistry {
    private DepartmentRouteRegistry() {}
}

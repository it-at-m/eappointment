export const tree = [
  {
    name: "src",
    type: "folder",
    children: [
      {
        name: "main",
        type: "folder",
        children: [
          {
            name: "java",
            type: "folder",
            children: [
              {
                name: "de",
                type: "folder",
                children: [
                  {
                    name: "muenchen",
                    type: "folder",
                    children: [
                      {
                        name: "zms",
                        type: "folder",
                        children: [
                          {
                            name: "department",
                            type: "folder",
                            children: [
                              {
                                name: "api",
                                type: "folder",
                                children: [
                                  {
                                    name: "DepartmentController.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/api/DepartmentController.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentRouteRegistry.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/api/DepartmentRouteRegistry.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentUseraccountController.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/api/DepartmentUseraccountController.java",
                                    language: "java",
                                  },
                                  {
                                    name: "OrganisationDepartmentController.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/api/OrganisationDepartmentController.java",
                                    language: "java",
                                  },
                                  {
                                    name: "ScopeDepartmentController.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/api/ScopeDepartmentController.java",
                                    language: "java",
                                  },
                                ],
                              },
                              {
                                name: "exception",
                                type: "folder",
                                children: [
                                  {
                                    name: "DepartmentInvalidIdException.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/exception/DepartmentInvalidIdException.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentNotFoundException.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/exception/DepartmentNotFoundException.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentScopeListNotEmptyException.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/exception/DepartmentScopeListNotEmptyException.java",
                                    language: "java",
                                  },
                                ],
                              },
                              {
                                name: "model",
                                type: "folder",
                                children: [
                                  {
                                    name: "Department.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/model/Department.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentEmail.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/model/DepartmentEmail.java",
                                    language: "java",
                                  },
                                ],
                              },
                              {
                                name: "repository",
                                type: "folder",
                                children: [
                                  {
                                    name: "DepartmentClusterRepository.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/repository/DepartmentClusterRepository.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentDayoffRepository.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/repository/DepartmentDayoffRepository.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentLinkRepository.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/repository/DepartmentLinkRepository.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentOrganisationRepository.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/repository/DepartmentOrganisationRepository.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentRepository.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/repository/DepartmentRepository.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentScopeRepository.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/repository/DepartmentScopeRepository.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentUseraccountRepository.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/repository/DepartmentUseraccountRepository.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentWorkstationRepository.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/repository/DepartmentWorkstationRepository.java",
                                    language: "java",
                                  },
                                ],
                              },
                              {
                                name: "service",
                                type: "folder",
                                children: [
                                  {
                                    name: "DepartmentAccessService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentAccessService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentByScopeFetchService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentByScopeFetchService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentClusterCreateService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentClusterCreateService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentCreateService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentCreateService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentDeleteService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentDeleteService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentFetchService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentFetchService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentListService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentListService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentOrganisationFetchService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentOrganisationFetchService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentReferenceLoader.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentReferenceLoader.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentScopeCreateService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentScopeCreateService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentUpdateService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentUpdateService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentUseraccountByRoleListService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentUseraccountByRoleListService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentUseraccountListService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentUseraccountListService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentWorkstationListService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentWorkstationListService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentWriteSupport.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/service/DepartmentWriteSupport.java",
                                    language: "java",
                                  },
                                ],
                              },
                              {
                                name: "validation",
                                type: "folder",
                                children: [
                                  {
                                    name: "DepartmentValidationException.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/validation/DepartmentValidationException.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentValidationService.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/validation/DepartmentValidationService.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentValidator.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/validation/DepartmentValidator.java",
                                    language: "java",
                                  },
                                  {
                                    name: "ValidateDepartment.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/validation/ValidateDepartment.java",
                                    language: "java",
                                  },
                                ],
                              },
                              {
                                name: "view",
                                type: "folder",
                                children: [
                                  {
                                    name: "ClusterView.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/view/ClusterView.java",
                                    language: "java",
                                  },
                                  {
                                    name: "ContactView.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/view/ContactView.java",
                                    language: "java",
                                  },
                                  {
                                    name: "DepartmentView.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/view/DepartmentView.java",
                                    language: "java",
                                  },
                                  {
                                    name: "OrganisationView.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/view/OrganisationView.java",
                                    language: "java",
                                  },
                                  {
                                    name: "ReferenceViews.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/view/ReferenceViews.java",
                                    language: "java",
                                  },
                                  {
                                    name: "ScopeView.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/view/ScopeView.java",
                                    language: "java",
                                  },
                                  {
                                    name: "UseraccountView.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/view/UseraccountView.java",
                                    language: "java",
                                  },
                                  {
                                    name: "WorkstationView.java",
                                    type: "file",
                                    path: "src/main/java/de/muenchen/zms/department/view/WorkstationView.java",
                                    language: "java",
                                  },
                                ],
                              },
                            ],
                          },
                        ],
                      },
                    ],
                  },
                ],
              },
            ],
          },
        ],
      },
    ],
  },
];

export const files = {
  "src/main/java/de/muenchen/zms/department/api/DepartmentController.java": {
    language: "java",
    content:
      'package de.muenchen.zms.department.api;\n\nimport de.muenchen.zms.department.service.DepartmentClusterCreateService;\nimport de.muenchen.zms.department.service.DepartmentDeleteService;\nimport de.muenchen.zms.department.service.DepartmentFetchService;\nimport de.muenchen.zms.department.service.DepartmentListService;\nimport de.muenchen.zms.department.service.DepartmentOrganisationFetchService;\nimport de.muenchen.zms.department.service.DepartmentScopeCreateService;\nimport de.muenchen.zms.department.service.DepartmentUpdateService;\nimport de.muenchen.zms.department.service.DepartmentWorkstationListService;\nimport de.muenchen.zms.department.view.ClusterView;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport de.muenchen.zms.department.view.OrganisationView;\nimport de.muenchen.zms.department.view.ScopeView;\nimport de.muenchen.zms.department.view.WorkstationView;\nimport java.util.List;\nimport org.springframework.http.ResponseEntity;\nimport org.springframework.security.access.prepost.PreAuthorize;\nimport org.springframework.web.bind.annotation.DeleteMapping;\nimport org.springframework.web.bind.annotation.GetMapping;\nimport org.springframework.web.bind.annotation.PathVariable;\nimport org.springframework.web.bind.annotation.PostMapping;\nimport org.springframework.web.bind.annotation.PutMapping;\nimport org.springframework.web.bind.annotation.RequestBody;\nimport org.springframework.web.bind.annotation.RequestMapping;\nimport org.springframework.web.bind.annotation.RequestParam;\nimport org.springframework.web.bind.annotation.RestController;\n\n/**\n * REST layer for /department/* routes.\n * today: DepartmentGet, DepartmentList, DepartmentUpdate, DepartmentDelete,\n * DepartmentAddScope, DepartmentAddCluster, OrganisationByDepartment, DepartmentWorkstationList\n */\n@RestController\n@RequestMapping("/api/v2/department")\npublic class DepartmentController {\n\n    private final DepartmentFetchService fetchService;\n    private final DepartmentListService listService;\n    private final DepartmentUpdateService updateService;\n    private final DepartmentDeleteService deleteService;\n    private final DepartmentScopeCreateService scopeCreateService;\n    private final DepartmentClusterCreateService clusterCreateService;\n    private final DepartmentOrganisationFetchService organisationFetchService;\n    private final DepartmentWorkstationListService workstationListService;\n\n    DepartmentController(\n            DepartmentFetchService fetchService,\n            DepartmentListService listService,\n            DepartmentUpdateService updateService,\n            DepartmentDeleteService deleteService,\n            DepartmentScopeCreateService scopeCreateService,\n            DepartmentClusterCreateService clusterCreateService,\n            DepartmentOrganisationFetchService organisationFetchService,\n            DepartmentWorkstationListService workstationListService) {\n        this.fetchService = fetchService;\n        this.listService = listService;\n        this.updateService = updateService;\n        this.deleteService = deleteService;\n        this.scopeCreateService = scopeCreateService;\n        this.clusterCreateService = clusterCreateService;\n        this.organisationFetchService = organisationFetchService;\n        this.workstationListService = workstationListService;\n    }\n\n    /** today: GET /department/ → DepartmentList */\n    @GetMapping\n    @PreAuthorize("hasAuthority(\'basic\')")\n    public ResponseEntity<List<DepartmentView>> listDepartments(\n            @RequestParam(defaultValue = "0") int resolveReferences) {\n        return ResponseEntity.ok(listService.list(resolveReferences));\n    }\n\n    /** today: GET /department/{id}/ → DepartmentGet */\n    @GetMapping("/{id}")\n    @PreAuthorize("hasAuthority(\'department\')")\n    public ResponseEntity<DepartmentView> getDepartment(\n            @PathVariable Long id, @RequestParam(defaultValue = "1") int resolveReferences) {\n        return ResponseEntity.ok(fetchService.getById(id, resolveReferences));\n    }\n\n    /** today: POST /department/{id}/ → DepartmentUpdate */\n    @PutMapping("/{id}")\n    @PreAuthorize("hasAuthority(\'department\')")\n    public ResponseEntity<DepartmentView> updateDepartment(\n            @PathVariable Long id, @RequestBody DepartmentView input) {\n        return ResponseEntity.ok(updateService.update(id, input));\n    }\n\n    /** today: DELETE /department/{id}/ → DepartmentDelete */\n    @DeleteMapping("/{id}")\n    @PreAuthorize("hasAuthority(\'department\')")\n    public ResponseEntity<DepartmentView> deleteDepartment(@PathVariable Long id) {\n        return ResponseEntity.ok(deleteService.delete(id));\n    }\n\n    /** today: POST /department/{id}/scope/ → DepartmentAddScope */\n    @PostMapping("/{id}/scope")\n    @PreAuthorize("hasAuthority(\'department\')")\n    public ResponseEntity<ScopeView> addScope(@PathVariable Long id, @RequestBody ScopeView input) {\n        return ResponseEntity.ok(scopeCreateService.addScope(id, input));\n    }\n\n    /** today: POST /department/{id}/cluster/ → DepartmentAddCluster */\n    @PostMapping("/{id}/cluster")\n    @PreAuthorize("hasAuthority(\'department\')")\n    public ResponseEntity<ClusterView> addCluster(@PathVariable Long id, @RequestBody ClusterView input) {\n        return ResponseEntity.ok(clusterCreateService.addCluster(id, input));\n    }\n\n    /** today: GET /department/{id}/organisation/ → OrganisationByDepartment */\n    @GetMapping("/{id}/organisation")\n    @PreAuthorize("hasAuthority(\'department\')")\n    public ResponseEntity<OrganisationView> getOrganisation(\n            @PathVariable Long id, @RequestParam(defaultValue = "0") int resolveReferences) {\n        return ResponseEntity.ok(organisationFetchService.getOrganisation(id, resolveReferences));\n    }\n\n    /** today: GET /department/{id}/workstation/ → DepartmentWorkstationList */\n    @GetMapping("/{id}/workstation")\n    @PreAuthorize("hasAuthority(\'useraccount\')")\n    public ResponseEntity<List<WorkstationView>> listWorkstations(\n            @PathVariable Long id, @RequestParam(defaultValue = "1") int resolveReferences) {\n        return ResponseEntity.ok(workstationListService.listWorkstations(id, resolveReferences));\n    }\n}\n',
  },
  "src/main/java/de/muenchen/zms/department/api/DepartmentRouteRegistry.java": {
    language: "java",
    content:
      "package de.muenchen.zms.department.api;\n\n/**\n * Route map for the department vertical slice (mirrors zmsbackend/routing.php excerpt).\n *\n * <pre>\n * GET    /api/v2/department/                              → DepartmentList\n * GET    /api/v2/department/{id}                          → DepartmentGet\n * PUT    /api/v2/department/{id}                          → DepartmentUpdate\n * DELETE /api/v2/department/{id}                          → DepartmentDelete\n * POST   /api/v2/department/{id}/scope                    → DepartmentAddScope\n * POST   /api/v2/department/{id}/cluster                  → DepartmentAddCluster\n * GET    /api/v2/department/{id}/organisation               → OrganisationByDepartment\n * GET    /api/v2/department/{ids}/useraccount             → UseraccountListByDepartments\n * GET    /api/v2/department/{id}/workstation              → DepartmentWorkstationList\n * POST   /api/v2/organisation/{id}/department             → OrganisationAddDepartment\n * GET    /api/v2/scope/{id}/department                    → DepartmentByScopeId\n * GET    /api/v2/role/{level}/department/{ids}/useraccount → UseraccountListByRoleAndDepartments\n * </pre>\n */\npublic final class DepartmentRouteRegistry {\n    private DepartmentRouteRegistry() {}\n}\n",
  },
  "src/main/java/de/muenchen/zms/department/api/DepartmentUseraccountController.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.api;\n\nimport de.muenchen.zms.department.service.DepartmentUseraccountByRoleListService;\nimport de.muenchen.zms.department.service.DepartmentUseraccountListService;\nimport de.muenchen.zms.department.view.UseraccountView;\nimport java.util.List;\nimport org.springframework.http.ResponseEntity;\nimport org.springframework.security.access.prepost.PreAuthorize;\nimport org.springframework.web.bind.annotation.GetMapping;\nimport org.springframework.web.bind.annotation.PathVariable;\nimport org.springframework.web.bind.annotation.RequestMapping;\nimport org.springframework.web.bind.annotation.RequestParam;\nimport org.springframework.web.bind.annotation.RestController;\n\n/**\n * today: GET /department/{ids}/useraccount/ → UseraccountListByDepartments\n * today: GET /role/{level}/department/{ids}/useraccount/ → UseraccountListByRoleAndDepartments\n */\n@RestController\npublic class DepartmentUseraccountController {\n\n    private final DepartmentUseraccountListService useraccountListService;\n    private final DepartmentUseraccountByRoleListService useraccountByRoleListService;\n\n    DepartmentUseraccountController(\n            DepartmentUseraccountListService useraccountListService,\n            DepartmentUseraccountByRoleListService useraccountByRoleListService) {\n        this.useraccountListService = useraccountListService;\n        this.useraccountByRoleListService = useraccountByRoleListService;\n    }\n\n    @GetMapping("/api/v2/department/{ids}/useraccount")\n    @PreAuthorize("hasAuthority(\'useraccount\')")\n    public ResponseEntity<List<UseraccountView>> listUseraccountsByDepartments(\n            @PathVariable String ids, @RequestParam(required = false) String query) {\n        return ResponseEntity.ok(useraccountListService.listByDepartmentIds(ids, query));\n    }\n\n    @GetMapping("/api/v2/role/{level}/department/{ids}/useraccount")\n    @PreAuthorize("hasAuthority(\'useraccount\')")\n    public ResponseEntity<List<UseraccountView>> listUseraccountsByRoleAndDepartments(\n            @PathVariable Long level, @PathVariable String ids) {\n        return ResponseEntity.ok(useraccountByRoleListService.listByRoleAndDepartmentIds(level, ids));\n    }\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/api/OrganisationDepartmentController.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.api;\n\nimport de.muenchen.zms.department.service.DepartmentCreateService;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport org.springframework.http.ResponseEntity;\nimport org.springframework.security.access.prepost.PreAuthorize;\nimport org.springframework.web.bind.annotation.PathVariable;\nimport org.springframework.web.bind.annotation.PostMapping;\nimport org.springframework.web.bind.annotation.RequestBody;\nimport org.springframework.web.bind.annotation.RequestMapping;\nimport org.springframework.web.bind.annotation.RestController;\n\n/** today: POST /organisation/{id}/department/ → OrganisationAddDepartment */\n@RestController\n@RequestMapping("/api/v2/organisation")\npublic class OrganisationDepartmentController {\n\n    private final DepartmentCreateService createService;\n\n    OrganisationDepartmentController(DepartmentCreateService createService) {\n        this.createService = createService;\n    }\n\n    @PostMapping("/{organisationId}/department")\n    @PreAuthorize("hasAuthority(\'department\')")\n    public ResponseEntity<DepartmentView> addDepartment(\n            @PathVariable Long organisationId, @RequestBody DepartmentView input) {\n        return ResponseEntity.ok(createService.createUnderOrganisation(organisationId, input));\n    }\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/api/ScopeDepartmentController.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.api;\n\nimport de.muenchen.zms.department.service.DepartmentByScopeFetchService;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport org.springframework.http.ResponseEntity;\nimport org.springframework.security.core.Authentication;\nimport org.springframework.web.bind.annotation.GetMapping;\nimport org.springframework.web.bind.annotation.PathVariable;\nimport org.springframework.web.bind.annotation.RequestMapping;\nimport org.springframework.web.bind.annotation.RequestParam;\nimport org.springframework.web.bind.annotation.RestController;\n\n/** today: GET /scope/{id}/department/ → DepartmentByScopeId */\n@RestController\n@RequestMapping("/api/v2/scope")\npublic class ScopeDepartmentController {\n\n    private final DepartmentByScopeFetchService byScopeFetchService;\n\n    ScopeDepartmentController(DepartmentByScopeFetchService byScopeFetchService) {\n        this.byScopeFetchService = byScopeFetchService;\n    }\n\n    @GetMapping("/{scopeId}/department")\n    public ResponseEntity<DepartmentView> getDepartmentByScope(\n            @PathVariable Long scopeId,\n            @RequestParam(defaultValue = "1") int resolveReferences,\n            Authentication authentication) {\n        boolean authenticated = authentication != null && authentication.isAuthenticated();\n        DepartmentView view =\n                byScopeFetchService.getByScopeId(scopeId, resolveReferences, !authenticated);\n        return ResponseEntity.ok(view);\n    }\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/exception/DepartmentInvalidIdException.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.exception;\n\nimport org.springframework.http.HttpStatus;\nimport org.springframework.web.bind.annotation.ResponseStatus;\n\n@ResponseStatus(HttpStatus.INTERNAL_SERVER_ERROR)\npublic class DepartmentInvalidIdException extends RuntimeException {\n    public DepartmentInvalidIdException() {\n        super("The given department ID is invalid, processing canceled");\n    }\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/exception/DepartmentNotFoundException.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.exception;\n\nimport org.springframework.http.HttpStatus;\nimport org.springframework.web.bind.annotation.ResponseStatus;\n\n@ResponseStatus(HttpStatus.NOT_FOUND)\npublic class DepartmentNotFoundException extends RuntimeException {\n    public DepartmentNotFoundException(Long id) {\n        super("Department not found: " + id);\n    }\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/exception/DepartmentScopeListNotEmptyException.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.exception;\n\nimport org.springframework.http.HttpStatus;\nimport org.springframework.web.bind.annotation.ResponseStatus;\n\n@ResponseStatus(HttpStatus.PRECONDITION_REQUIRED)\npublic class DepartmentScopeListNotEmptyException extends RuntimeException {\n    public DepartmentScopeListNotEmptyException(Long id) {\n        super("Department " + id + " still has scopes or clusters");\n    }\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/model/Department.java": {
    language: "java",
    content:
      'package de.muenchen.zms.department.model;\n\nimport jakarta.persistence.Column;\nimport jakarta.persistence.Entity;\nimport jakarta.persistence.Id;\nimport jakarta.persistence.Table;\n\n@Entity\n@Table(name = "department") // today: behoerde\npublic class Department {\n\n    @Id\n    @Column(name = "id") // today: BehoerdenID\n    private Long id;\n\n    @Column(name = "name", nullable = false) // today: Name\n    private String name;\n\n    @Column(name = "address") // today: Adresse\n    private String address;\n\n    @Column(name = "contact_name") // today: Ansprechpartner\n    private String contactName;\n\n    @Column(name = "organisation_id") // today: OrganisationsID\n    private Long organisationId;\n\n    @Column(name = "owner_id") // today: KundenID\n    private Long ownerId;\n\n    public Long getId() { return id; }\n    public void setId(Long id) { this.id = id; }\n    public String getName() { return name; }\n    public void setName(String name) { this.name = name; }\n    public String getAddress() { return address; }\n    public void setAddress(String address) { this.address = address; }\n    public String getContactName() { return contactName; }\n    public void setContactName(String contactName) { this.contactName = contactName; }\n    public Long getOrganisationId() { return organisationId; }\n    public void setOrganisationId(Long organisationId) { this.organisationId = organisationId; }\n    public Long getOwnerId() { return ownerId; }\n    public void setOwnerId(Long ownerId) { this.ownerId = ownerId; }\n}\n',
  },
  "src/main/java/de/muenchen/zms/department/model/DepartmentEmail.java": {
    language: "java",
    content:
      'package de.muenchen.zms.department.model;\n\nimport jakarta.persistence.Column;\nimport jakarta.persistence.Entity;\nimport jakarta.persistence.Id;\nimport jakarta.persistence.Table;\n\n@Entity\n@Table(name = "email")\npublic class DepartmentEmail {\n\n    @Id\n    @Column(name = "email_id") // today: emailID\n    private Long id;\n\n    @Column(name = "department_id") // today: BehoerdenID\n    private Long departmentId;\n\n    @Column(name = "sender_address") // today: absenderadresse\n    private String senderAddress;\n\n    @Column(name = "send_reminder") // today: send_reminder\n    private Boolean sendReminderEnabled;\n\n    @Column(name = "send_reminder_minutes_before")\n    private Integer sendReminderMinutesBefore;\n\n    public Long getId() { return id; }\n    public void setId(Long id) { this.id = id; }\n    public Long getDepartmentId() { return departmentId; }\n    public void setDepartmentId(Long departmentId) { this.departmentId = departmentId; }\n    public String getSenderAddress() { return senderAddress; }\n    public void setSenderAddress(String senderAddress) { this.senderAddress = senderAddress; }\n    public Boolean getSendReminderEnabled() { return sendReminderEnabled; }\n    public void setSendReminderEnabled(Boolean sendReminderEnabled) { this.sendReminderEnabled = sendReminderEnabled; }\n    public Integer getSendReminderMinutesBefore() { return sendReminderMinutesBefore; }\n    public void setSendReminderMinutesBefore(Integer minutes) { this.sendReminderMinutesBefore = minutes; }\n}\n',
  },
  "src/main/java/de/muenchen/zms/department/repository/DepartmentClusterRepository.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.repository;\n\nimport de.muenchen.zms.department.view.ReferenceViews.ClusterReferenceView;\nimport java.util.List;\nimport org.springframework.data.jpa.repository.Query;\nimport org.springframework.data.repository.Repository;\nimport org.springframework.data.repository.query.Param;\n\n/** today: zmsbackend\\\\Cluster\\\\Service\\\\Cluster::readByDepartmentId, writeEntity */\npublic interface DepartmentClusterRepository extends Repository<Object, Long> {\n\n    @Query("""\n        SELECT new de.muenchen.zms.department.view.ReferenceViews$ClusterReferenceView(c.id)\n        FROM Cluster c\n        WHERE c.departmentId = :departmentId\n        """)\n    List<ClusterReferenceView> findByDepartmentId(@Param("departmentId") Long departmentId);\n\n    @Query("""\n        SELECT COUNT(c) FROM Cluster c WHERE c.departmentId = :departmentId\n        """)\n    long countByDepartmentId(@Param("departmentId") Long departmentId);\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/repository/DepartmentDayoffRepository.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.repository;\n\nimport de.muenchen.zms.department.view.ReferenceViews.DayoffView;\nimport java.util.List;\nimport org.springframework.data.jpa.repository.Query;\nimport org.springframework.data.repository.Repository;\nimport org.springframework.data.repository.query.Param;\n\n/** today: zmsbackend\\\\Dayoff\\\\Service\\\\DayOff::readOnlyByDepartmentId */\npublic interface DepartmentDayoffRepository extends Repository<Object, Long> {\n\n    @Query("""\n        SELECT new de.muenchen.zms.department.view.ReferenceViews$DayoffView(\n            d.dateEpoch, d.name)\n        FROM DepartmentDayoff d\n        WHERE d.departmentId = :departmentId\n        """)\n    List<DayoffView> findByDepartmentId(@Param("departmentId") Long departmentId);\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/repository/DepartmentLinkRepository.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.repository;\n\nimport de.muenchen.zms.department.view.ReferenceViews.LinkView;\nimport java.util.List;\nimport org.springframework.data.jpa.repository.Query;\nimport org.springframework.data.repository.Repository;\nimport org.springframework.data.repository.query.Param;\n\n/** today: zmsbackend\\\\Link\\\\Service\\\\Link::readByDepartmentId */\npublic interface DepartmentLinkRepository extends Repository<Object, Long> {\n\n    @Query("""\n        SELECT new de.muenchen.zms.department.view.ReferenceViews$LinkView(\n            l.name, l.url, l.target)\n        FROM DepartmentLink l\n        WHERE l.departmentId = :departmentId\n        """)\n    List<LinkView> findByDepartmentId(@Param("departmentId") Long departmentId);\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/repository/DepartmentOrganisationRepository.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.repository;\n\nimport de.muenchen.zms.department.view.OrganisationView;\nimport java.util.Optional;\nimport org.springframework.data.jpa.repository.Query;\nimport org.springframework.data.repository.Repository;\nimport org.springframework.data.repository.query.Param;\n\n/** today: zmsbackend\\\\Organisation\\\\Service\\\\Organisation::readByDepartmentId */\npublic interface DepartmentOrganisationRepository extends Repository<Object, Long> {\n\n    @Query("""\n        SELECT new de.muenchen.zms.department.view.OrganisationView(o.id, o.name, NULL)\n        FROM Organisation o\n        JOIN Department d ON d.organisationId = o.id\n        WHERE d.id = :departmentId\n        """)\n    Optional<OrganisationView> findByDepartmentId(@Param("departmentId") Long departmentId);\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/repository/DepartmentRepository.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.repository;\n\nimport de.muenchen.zms.department.model.Department;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport java.util.List;\nimport java.util.Optional;\nimport org.springframework.data.jpa.repository.JpaRepository;\nimport org.springframework.data.jpa.repository.Query;\nimport org.springframework.data.repository.query.Param;\n\n/** today: zmsbackend\\\\Department\\\\Service\\\\Department + zmsbackend\\\\Department\\\\Repository\\\\Department */\npublic interface DepartmentRepository extends JpaRepository<Department, Long> {\n\n    @Query("""\n        SELECT new de.muenchen.zms.department.view.DepartmentView(\n            d.id, d.name, d.address, d.contactName,\n            e.senderAddress, e.sendReminderEnabled, e.sendReminderMinutesBefore)\n        FROM Department d\n        LEFT JOIN DepartmentEmail e ON e.departmentId = d.id\n        WHERE d.id = :id\n        """)\n    Optional<DepartmentView> findViewById(@Param("id") Long id);\n\n    @Query("""\n        SELECT new de.muenchen.zms.department.view.DepartmentView(\n            d.id, d.name, d.address, d.contactName,\n            e.senderAddress, e.sendReminderEnabled, e.sendReminderMinutesBefore)\n        FROM Department d\n        LEFT JOIN DepartmentEmail e ON e.departmentId = d.id\n        ORDER BY d.name\n        """)\n    List<DepartmentView> findAllViews();\n\n    @Query("""\n        SELECT new de.muenchen.zms.department.view.DepartmentView(\n            d.id, d.name, d.address, d.contactName,\n            e.senderAddress, e.sendReminderEnabled, e.sendReminderMinutesBefore)\n        FROM Department d\n        LEFT JOIN DepartmentEmail e ON e.departmentId = d.id\n        JOIN Scope s ON s.departmentId = d.id\n        WHERE s.id = :scopeId\n        """)\n    Optional<DepartmentView> findViewByScopeId(@Param("scopeId") Long scopeId);\n\n    List<Department> findByOrganisationId(Long organisationId);\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/repository/DepartmentScopeRepository.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.repository;\n\nimport de.muenchen.zms.department.view.ReferenceViews.ScopeReferenceView;\nimport de.muenchen.zms.department.view.ScopeView;\nimport java.util.List;\nimport org.springframework.data.jpa.repository.Query;\nimport org.springframework.data.repository.Repository;\nimport org.springframework.data.repository.query.Param;\n\n/** today: zmsbackend\\\\Scope\\\\Service\\\\Scope::readByDepartmentId, writeEntity */\npublic interface DepartmentScopeRepository extends Repository<Object, Long> {\n\n    @Query("""\n        SELECT new de.muenchen.zms.department.view.ReferenceViews$ScopeReferenceView(\n            s.id, s.shortName, CONCAT(\'/scope/\', s.id, \'/\'))\n        FROM Scope s\n        WHERE s.departmentId = :departmentId\n        ORDER BY s.contactName\n        """)\n    List<ScopeReferenceView> findReferencesByDepartmentId(@Param("departmentId") Long departmentId);\n\n    @Query("""\n        SELECT COUNT(s) FROM Scope s WHERE s.departmentId = :departmentId\n        """)\n    long countByDepartmentId(@Param("departmentId") Long departmentId);\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/repository/DepartmentUseraccountRepository.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.repository;\n\nimport de.muenchen.zms.department.view.UseraccountView;\nimport java.util.List;\nimport org.springframework.data.jpa.repository.Query;\nimport org.springframework.data.repository.Repository;\nimport org.springframework.data.repository.query.Param;\n\n/** today: zmsbackend\\\\Useraccount\\\\Service\\\\Useraccount::readSearchByDepartmentIds, readListByRoleAndDepartmentIds */\npublic interface DepartmentUseraccountRepository extends Repository<Object, Long> {\n\n    @Query("""\n        SELECT DISTINCT new de.muenchen.zms.department.view.UseraccountView(u.id, u.name, NULL)\n        FROM Useraccount u\n        JOIN u.departmentAssignments da\n        WHERE da.departmentId IN :departmentIds\n          AND (:query IS NULL OR u.name LIKE CONCAT(\'%\', :query, \'%\') OR CAST(u.id AS string) = :query)\n        ORDER BY u.name\n        """)\n    List<UseraccountView> searchByDepartmentIds(\n            @Param("departmentIds") List<Long> departmentIds, @Param("query") String query);\n\n    @Query("""\n        SELECT DISTINCT new de.muenchen.zms.department.view.UseraccountView(u.id, u.name, NULL)\n        FROM Useraccount u\n        JOIN u.departmentAssignments da\n        JOIN u.roles r\n        WHERE da.departmentId IN :departmentIds\n          AND r.level = :roleLevel\n        ORDER BY u.name\n        """)\n    List<UseraccountView> findByRoleAndDepartmentIds(\n            @Param("roleLevel") Long roleLevel, @Param("departmentIds") List<Long> departmentIds);\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/repository/DepartmentWorkstationRepository.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.repository;\n\nimport de.muenchen.zms.department.view.WorkstationView;\nimport java.util.List;\nimport org.springframework.data.jpa.repository.Query;\nimport org.springframework.data.repository.Repository;\nimport org.springframework.data.repository.query.Param;\n\n/** today: zmsbackend\\\\Workstation\\\\Service\\\\Workstation::readCollectionByDepartmentId */\npublic interface DepartmentWorkstationRepository extends Repository<Object, Long> {\n\n    @Query("""\n        SELECT new de.muenchen.zms.department.view.WorkstationView(w.id, w.name)\n        FROM Workstation w\n        WHERE w.departmentId = :departmentId\n        ORDER BY w.name\n        """)\n    List<WorkstationView> findByDepartmentId(@Param("departmentId") Long departmentId);\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentAccessService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.exception.DepartmentNotFoundException;\nimport de.muenchen.zms.department.model.Department;\nimport de.muenchen.zms.department.repository.DepartmentRepository;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport java.util.List;\nimport org.springframework.stereotype.Service;\n\n/** today: zmsbackend\\\\Helper\\\\User::checkDepartment / checkDepartments */\n@Service\npublic class DepartmentAccessService {\n\n    private final DepartmentRepository repository;\n\n    DepartmentAccessService(DepartmentRepository repository) {\n        this.repository = repository;\n    }\n\n    public DepartmentView requireDepartment(Long id) {\n        return repository\n                .findViewById(id)\n                .orElseThrow(() -> new DepartmentNotFoundException(id));\n    }\n\n    public List<Long> filterAccessibleDepartmentIds(List<Long> requested) {\n        // today: superuser bypass; otherwise intersect with assigned departments\n        return requested.stream().filter(repository::existsById).toList();\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentByScopeFetchService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.exception.DepartmentNotFoundException;\nimport de.muenchen.zms.department.repository.DepartmentRepository;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Department\\\\Api\\\\DepartmentByScopeId, zmsbackend\\\\Department\\\\Service\\\\Department::readByScopeId */\n@Service\npublic class DepartmentByScopeFetchService {\n\n    private final DepartmentRepository repository;\n    private final DepartmentReferenceLoader referenceLoader;\n\n    DepartmentByScopeFetchService(\n            DepartmentRepository repository, DepartmentReferenceLoader referenceLoader) {\n        this.repository = repository;\n        this.referenceLoader = referenceLoader;\n    }\n\n    @Transactional(readOnly = true)\n    public DepartmentView getByScopeId(Long scopeId, int resolveReferences, boolean reduceForAnonymous) {\n        DepartmentView view =\n                repository\n                        .findViewByScopeId(scopeId)\n                        .map(v -> referenceLoader.withReferences(v, resolveReferences))\n                        .orElseThrow(() -> new DepartmentNotFoundException(scopeId));\n        return reduceForAnonymous ? view.withLessData() : view;\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentClusterCreateService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.view.ClusterView;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Department\\\\Api\\\\DepartmentAddCluster, zmsbackend\\\\Cluster\\\\Service\\\\Cluster::writeEntity */\n@Service\npublic class DepartmentClusterCreateService {\n\n    private final DepartmentFetchService fetchService;\n\n    DepartmentClusterCreateService(DepartmentFetchService fetchService) {\n        this.fetchService = fetchService;\n    }\n\n    @Transactional\n    public ClusterView addCluster(Long departmentId, ClusterView input) {\n        fetchService.getById(departmentId, 1);\n        // today: validate cluster JSON against zmsentities/schema/cluster.json, then persist\n        return input;\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentCreateService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.model.Department;\nimport de.muenchen.zms.department.repository.DepartmentRepository;\nimport de.muenchen.zms.department.validation.DepartmentValidationService;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Organisation\\\\Api\\\\OrganisationAddDepartment, zmsbackend\\\\Department\\\\Service\\\\Department::writeEntity */\n@Service\npublic class DepartmentCreateService {\n\n    private final DepartmentRepository repository;\n    private final DepartmentFetchService fetchService;\n    private final DepartmentWriteSupport writeSupport;\n    private final DepartmentValidationService validationService;\n\n    DepartmentCreateService(\n            DepartmentRepository repository,\n            DepartmentFetchService fetchService,\n            DepartmentWriteSupport writeSupport,\n            DepartmentValidationService validationService) {\n        this.repository = repository;\n        this.fetchService = fetchService;\n        this.writeSupport = writeSupport;\n        this.validationService = validationService;\n    }\n\n    @Transactional\n    public DepartmentView createUnderOrganisation(Long organisationId, DepartmentView input) {\n        validationService.validateForWrite(input);\n        Department entity = new Department();\n        entity.setOrganisationId(organisationId);\n        entity.setOwnerId(writeSupport.resolveOwnerIdForOrganisation(organisationId));\n        writeSupport.applyView(entity, input);\n        Department saved = repository.save(entity);\n        writeSupport.writeNestedEntities(saved.getId(), input);\n        return fetchService.getById(saved.getId(), 0);\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentDeleteService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.exception.DepartmentNotFoundException;\nimport de.muenchen.zms.department.exception.DepartmentScopeListNotEmptyException;\nimport de.muenchen.zms.department.repository.DepartmentClusterRepository;\nimport de.muenchen.zms.department.repository.DepartmentRepository;\nimport de.muenchen.zms.department.repository.DepartmentScopeRepository;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Department\\\\Api\\\\DepartmentDelete, zmsbackend\\\\Department\\\\Service\\\\Department::deleteEntity */\n@Service\npublic class DepartmentDeleteService {\n\n    private final DepartmentRepository repository;\n    private final DepartmentScopeRepository scopeRepository;\n    private final DepartmentClusterRepository clusterRepository;\n    private final DepartmentFetchService fetchService;\n\n    DepartmentDeleteService(\n            DepartmentRepository repository,\n            DepartmentScopeRepository scopeRepository,\n            DepartmentClusterRepository clusterRepository,\n            DepartmentFetchService fetchService) {\n        this.repository = repository;\n        this.scopeRepository = scopeRepository;\n        this.clusterRepository = clusterRepository;\n        this.fetchService = fetchService;\n    }\n\n    @Transactional\n    public DepartmentView delete(Long id) {\n        DepartmentView department = fetchService.getById(id, 1);\n        if (scopeRepository.countByDepartmentId(id) > 0\n                || clusterRepository.countByDepartmentId(id) > 0) {\n            throw new DepartmentScopeListNotEmptyException(id);\n        }\n        if (!repository.existsById(id)) {\n            throw new DepartmentNotFoundException(id);\n        }\n        repository.deleteById(id);\n        return department;\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentFetchService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.exception.DepartmentNotFoundException;\nimport de.muenchen.zms.department.repository.DepartmentRepository;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Department\\\\Api\\\\DepartmentGet, zmsbackend\\\\Department\\\\Service\\\\Department::readEntity */\n@Service\npublic class DepartmentFetchService {\n\n    private final DepartmentRepository repository;\n    private final DepartmentReferenceLoader referenceLoader;\n\n    DepartmentFetchService(DepartmentRepository repository, DepartmentReferenceLoader referenceLoader) {\n        this.repository = repository;\n        this.referenceLoader = referenceLoader;\n    }\n\n    @Transactional(readOnly = true)\n    public DepartmentView getById(Long id, int resolveReferences) {\n        return repository\n                .findViewById(id)\n                .map(view -> referenceLoader.withReferences(view, resolveReferences))\n                .orElseThrow(() -> new DepartmentNotFoundException(id));\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentListService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.repository.DepartmentRepository;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport java.util.List;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Department\\\\Api\\\\DepartmentList, zmsbackend\\\\Department\\\\Service\\\\Department::readList */\n@Service\npublic class DepartmentListService {\n\n    private final DepartmentRepository repository;\n    private final DepartmentReferenceLoader referenceLoader;\n\n    DepartmentListService(DepartmentRepository repository, DepartmentReferenceLoader referenceLoader) {\n        this.repository = repository;\n        this.referenceLoader = referenceLoader;\n    }\n\n    @Transactional(readOnly = true)\n    public List<DepartmentView> list(int resolveReferences) {\n        return repository.findAllViews().stream()\n                .map(view -> referenceLoader.withReferences(view, resolveReferences))\n                .toList();\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentOrganisationFetchService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.exception.DepartmentNotFoundException;\nimport de.muenchen.zms.department.repository.DepartmentOrganisationRepository;\nimport de.muenchen.zms.department.view.OrganisationView;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Organisation\\\\Api\\\\OrganisationByDepartment, zmsbackend\\\\Organisation\\\\Service\\\\Organisation::readByDepartmentId */\n@Service\npublic class DepartmentOrganisationFetchService {\n\n    private final DepartmentFetchService fetchService;\n    private final DepartmentOrganisationRepository organisationRepository;\n\n    DepartmentOrganisationFetchService(\n            DepartmentFetchService fetchService, DepartmentOrganisationRepository organisationRepository) {\n        this.fetchService = fetchService;\n        this.organisationRepository = organisationRepository;\n    }\n\n    @Transactional(readOnly = true)\n    public OrganisationView getOrganisation(Long departmentId, int resolveReferences) {\n        fetchService.getById(departmentId, 0);\n        return organisationRepository\n                .findByDepartmentId(departmentId)\n                .orElseThrow(() -> new DepartmentNotFoundException(departmentId));\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentReferenceLoader.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.repository.DepartmentClusterRepository;\nimport de.muenchen.zms.department.repository.DepartmentDayoffRepository;\nimport de.muenchen.zms.department.repository.DepartmentLinkRepository;\nimport de.muenchen.zms.department.repository.DepartmentScopeRepository;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport de.muenchen.zms.department.view.ReferenceViews.ClusterReferenceView;\nimport de.muenchen.zms.department.view.ReferenceViews.DayoffView;\nimport de.muenchen.zms.department.view.ReferenceViews.LinkView;\nimport de.muenchen.zms.department.view.ReferenceViews.ScopeReferenceView;\nimport java.util.List;\nimport org.springframework.stereotype.Component;\n\n/** today: zmsbackend\\\\Department\\\\Service\\\\Department::readResolvedReferences */\n@Component\npublic class DepartmentReferenceLoader {\n\n    private final DepartmentLinkRepository linkRepository;\n    private final DepartmentScopeRepository scopeRepository;\n    private final DepartmentClusterRepository clusterRepository;\n    private final DepartmentDayoffRepository dayoffRepository;\n\n    DepartmentReferenceLoader(\n            DepartmentLinkRepository linkRepository,\n            DepartmentScopeRepository scopeRepository,\n            DepartmentClusterRepository clusterRepository,\n            DepartmentDayoffRepository dayoffRepository) {\n        this.linkRepository = linkRepository;\n        this.scopeRepository = scopeRepository;\n        this.clusterRepository = clusterRepository;\n        this.dayoffRepository = dayoffRepository;\n    }\n\n    public DepartmentView withReferences(DepartmentView view, int resolveReferences) {\n        if (resolveReferences <= 0) {\n            return view;\n        }\n        List<ScopeReferenceView> scopes =\n                scopeRepository.findReferencesByDepartmentId(view.id());\n        List<ClusterReferenceView> clusters = List.of();\n        List<DayoffView> dayoffs = List.of();\n        if (resolveReferences > 0) {\n            clusters = clusterRepository.findByDepartmentId(view.id());\n            dayoffs = dayoffRepository.findByDepartmentId(view.id());\n        }\n        List<LinkView> links = linkRepository.findByDepartmentId(view.id());\n        return view.withReferences(scopes, clusters, links, dayoffs);\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentScopeCreateService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.view.ScopeView;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Department\\\\Api\\\\DepartmentAddScope, zmsbackend\\\\Scope\\\\Service\\\\Scope::writeEntity */\n@Service\npublic class DepartmentScopeCreateService {\n\n    private final DepartmentFetchService fetchService;\n\n    DepartmentScopeCreateService(DepartmentFetchService fetchService) {\n        this.fetchService = fetchService;\n    }\n\n    @Transactional\n    public ScopeView addScope(Long departmentId, ScopeView input) {\n        fetchService.getById(departmentId, 1);\n        // today: validate scope JSON against zmsentities/schema/scope.json, then persist\n        return input;\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentUpdateService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.exception.DepartmentNotFoundException;\nimport de.muenchen.zms.department.model.Department;\nimport de.muenchen.zms.department.repository.DepartmentRepository;\nimport de.muenchen.zms.department.validation.DepartmentValidationService;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Department\\\\Api\\\\DepartmentUpdate, zmsbackend\\\\Department\\\\Service\\\\Department::updateEntity */\n@Service\npublic class DepartmentUpdateService {\n\n    private final DepartmentRepository repository;\n    private final DepartmentFetchService fetchService;\n    private final DepartmentWriteSupport writeSupport;\n    private final DepartmentValidationService validationService;\n\n    DepartmentUpdateService(\n            DepartmentRepository repository,\n            DepartmentFetchService fetchService,\n            DepartmentWriteSupport writeSupport,\n            DepartmentValidationService validationService) {\n        this.repository = repository;\n        this.fetchService = fetchService;\n        this.writeSupport = writeSupport;\n        this.validationService = validationService;\n    }\n\n    @Transactional\n    public DepartmentView update(Long id, DepartmentView input) {\n        validationService.validateForWrite(input);\n        Department entity =\n                repository.findById(id).orElseThrow(() -> new DepartmentNotFoundException(id));\n        writeSupport.applyView(entity, input);\n        repository.save(entity);\n        writeSupport.writeNestedEntities(id, input);\n        return fetchService.getById(id, 0);\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentUseraccountByRoleListService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.repository.DepartmentUseraccountRepository;\nimport de.muenchen.zms.department.view.UseraccountView;\nimport java.util.List;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Useraccount\\\\Api\\\\UseraccountListByRoleAndDepartments */\n@Service\npublic class DepartmentUseraccountByRoleListService {\n\n    private final DepartmentUseraccountRepository useraccountRepository;\n    private final DepartmentAccessService accessService;\n\n    DepartmentUseraccountByRoleListService(\n            DepartmentUseraccountRepository useraccountRepository, DepartmentAccessService accessService) {\n        this.useraccountRepository = useraccountRepository;\n        this.accessService = accessService;\n    }\n\n    @Transactional(readOnly = true)\n    public List<UseraccountView> listByRoleAndDepartmentIds(Long roleLevel, String rawIds) {\n        List<Long> requested = DepartmentUseraccountListService.parseIds(rawIds);\n        List<Long> allowed = accessService.filterAccessibleDepartmentIds(requested);\n        return useraccountRepository.findByRoleAndDepartmentIds(roleLevel, allowed);\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentUseraccountListService.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.repository.DepartmentUseraccountRepository;\nimport de.muenchen.zms.department.view.UseraccountView;\nimport java.util.Arrays;\nimport java.util.List;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Useraccount\\\\Api\\\\UseraccountListByDepartments, zmsbackend\\\\Useraccount\\\\Service\\\\Useraccount::readSearchByDepartmentIds */\n@Service\npublic class DepartmentUseraccountListService {\n\n    private final DepartmentUseraccountRepository useraccountRepository;\n    private final DepartmentAccessService accessService;\n\n    DepartmentUseraccountListService(\n            DepartmentUseraccountRepository useraccountRepository, DepartmentAccessService accessService) {\n        this.useraccountRepository = useraccountRepository;\n        this.accessService = accessService;\n    }\n\n    @Transactional(readOnly = true)\n    public List<UseraccountView> listByDepartmentIds(String rawIds, String query) {\n        List<Long> requested = parseIds(rawIds);\n        List<Long> allowed = accessService.filterAccessibleDepartmentIds(requested);\n        return useraccountRepository.searchByDepartmentIds(allowed, query);\n    }\n\n    static List<Long> parseIds(String rawIds) {\n        return Arrays.stream(rawIds.split(","))\n                .map(String::trim)\n                .filter(s -> !s.isEmpty())\n                .map(Long::valueOf)\n                .toList();\n    }\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentWorkstationListService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.repository.DepartmentWorkstationRepository;\nimport de.muenchen.zms.department.view.WorkstationView;\nimport java.util.List;\nimport org.springframework.stereotype.Service;\nimport org.springframework.transaction.annotation.Transactional;\n\n/** today: zmsbackend\\\\Department\\\\Api\\\\DepartmentWorkstationList, zmsbackend\\\\Workstation\\\\Service\\\\Workstation::readCollectionByDepartmentId */\n@Service\npublic class DepartmentWorkstationListService {\n\n    private final DepartmentFetchService fetchService;\n    private final DepartmentWorkstationRepository workstationRepository;\n\n    DepartmentWorkstationListService(\n            DepartmentFetchService fetchService, DepartmentWorkstationRepository workstationRepository) {\n        this.fetchService = fetchService;\n        this.workstationRepository = workstationRepository;\n    }\n\n    @Transactional(readOnly = true)\n    public List<WorkstationView> listWorkstations(Long departmentId, int resolveReferences) {\n        fetchService.getById(departmentId, 0);\n        return workstationRepository.findByDepartmentId(departmentId);\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/service/DepartmentWriteSupport.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.service;\n\nimport de.muenchen.zms.department.model.Department;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport org.springframework.stereotype.Component;\n\n/** today: zmsbackend\\\\Department\\\\Service\\\\Department writeDepartmentLinks/Dayoffs/Mail helpers */\n@Component\npublic class DepartmentWriteSupport {\n\n    public Long resolveOwnerIdForOrganisation(Long organisationId) {\n        // today: (new Owner())->readByOrganisationId($parentId)\n        return organisationId;\n    }\n\n    public void applyView(Department entity, DepartmentView view) {\n        entity.setName(view.name());\n        if (view.contact() != null) {\n            entity.setAddress(view.contact().street());\n            entity.setContactName(view.contact().name());\n        }\n    }\n\n    public void writeNestedEntities(Long departmentId, DepartmentView view) {\n        // today: writeDepartmentLinks, writeDepartmentDayoffs, writeDepartmentMail\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/validation/DepartmentValidationException.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.validation;\n\nimport org.springframework.http.HttpStatus;\nimport org.springframework.web.bind.annotation.ResponseStatus;\n\n@ResponseStatus(HttpStatus.BAD_REQUEST)\npublic class DepartmentValidationException extends RuntimeException {\n\n    public DepartmentValidationException(String message) {\n        super(message);\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/validation/DepartmentValidationService.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.validation;\n\nimport de.muenchen.zms.department.view.DepartmentView;\nimport org.springframework.stereotype.Component;\n\n/** today: {@code $entity->testValid()} replaced by imperative rules on {@link DepartmentView} */\n@Component\npublic class DepartmentValidationService {\n\n    private final ValidateDepartment validator;\n\n    DepartmentValidationService(ValidateDepartment validator) {\n        this.validator = validator;\n    }\n\n    public void validateForWrite(DepartmentView view) {\n        validator.validate(view);\n    }\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/validation/DepartmentValidator.java":
    {
      language: "java",
      content:
        "package de.muenchen.zms.department.validation;\n\nimport de.muenchen.zms.department.view.DepartmentView;\n\npublic interface DepartmentValidator {\n    void validate(DepartmentView view) throws DepartmentValidationException;\n}\n",
    },
  "src/main/java/de/muenchen/zms/department/validation/ValidateDepartment.java":
    {
      language: "java",
      content:
        'package de.muenchen.zms.department.validation;\n\nimport de.muenchen.zms.department.repository.DepartmentRepository;\nimport de.muenchen.zms.department.view.DepartmentView;\nimport java.util.regex.Pattern;\nimport org.springframework.stereotype.Component;\n\n/**\n * Validates {@link DepartmentView} before write operations.\n *\n * <p>RefArch pattern: {@code validation/rules/Validate*.java} with explicit {@code validate(view)}\n * calls (like {@code ValidateLinks}, {@code ValidateLanguages}). Rules from today\'s\n * {@code department.json} (name, email pattern, …) are expressed here in Java — not via JSON Schema.\n */\n@Component\npublic class ValidateDepartment implements DepartmentValidator {\n\n    private static final Pattern EMAIL =\n            Pattern.compile("^[a-zA-Z0-9_\\\\-\\\\.]{2,}@[a-zA-Z0-9_\\\\-\\\\.]{2,}\\\\.[a-z]{2,}$|^$");\n\n    private final DepartmentRepository repository;\n\n    ValidateDepartment(DepartmentRepository repository) {\n        this.repository = repository;\n    }\n\n    @Override\n    public void validate(DepartmentView view) throws DepartmentValidationException {\n        if (view == null) {\n            throw new DepartmentValidationException("Department payload cannot be null.");\n        }\n        if (view.name() == null || view.name().isBlank()) {\n            throw new DepartmentValidationException("Name cannot be null or empty.");\n        }\n        if (view.email() != null && !EMAIL.matcher(view.email()).matches()) {\n            throw new DepartmentValidationException(\n                    "Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein");\n        }\n        if (view.sendEmailReminderMinutesBefore() != null && view.sendEmailReminderMinutesBefore() < 0) {\n            throw new DepartmentValidationException("Reminder minutes before must be a non-negative number.");\n        }\n        if (view.id() != null && !repository.existsById(view.id())) {\n            throw new DepartmentValidationException("Department id does not exist: " + view.id());\n        }\n    }\n}\n',
    },
  "src/main/java/de/muenchen/zms/department/view/ClusterView.java": {
    language: "java",
    content:
      "package de.muenchen.zms.department.view;\n\nimport com.fasterxml.jackson.annotation.JsonInclude;\nimport de.muenchen.zms.department.view.ReferenceViews.ScopeReferenceView;\nimport java.util.List;\n\n@JsonInclude(JsonInclude.Include.NON_NULL)\npublic record ClusterView(Long id, String name, List<ScopeReferenceView> scopes) {}\n",
  },
  "src/main/java/de/muenchen/zms/department/view/ContactView.java": {
    language: "java",
    content:
      "package de.muenchen.zms.department.view;\n\nimport com.fasterxml.jackson.annotation.JsonInclude;\n\n@JsonInclude(JsonInclude.Include.NON_NULL)\npublic record ContactView(String country, String name, String street, String city) {}\n",
  },
  "src/main/java/de/muenchen/zms/department/view/DepartmentView.java": {
    language: "java",
    content:
      'package de.muenchen.zms.department.view;\n\nimport com.fasterxml.jackson.annotation.JsonInclude;\nimport de.muenchen.zms.department.view.ReferenceViews.ClusterReferenceView;\nimport de.muenchen.zms.department.view.ReferenceViews.DayoffView;\nimport de.muenchen.zms.department.view.ReferenceViews.LinkView;\nimport de.muenchen.zms.department.view.ReferenceViews.ScopeReferenceView;\nimport java.util.List;\n\n@JsonInclude(JsonInclude.Include.NON_NULL)\npublic record DepartmentView(\n        Long id,\n        String name,\n        ContactView contact,\n        String email,\n        Boolean sendEmailReminderEnabled,\n        Integer sendEmailReminderMinutesBefore,\n        List<ScopeReferenceView> scopes,\n        List<ClusterReferenceView> clusters,\n        List<LinkView> links,\n        List<DayoffView> dayoff) {\n\n    public DepartmentView(\n            Long id,\n            String name,\n            String address,\n            String contactName,\n            String email,\n            Boolean sendEmailReminderEnabled,\n            Integer sendReminderMinutesBefore) {\n        this(\n                id,\n                name,\n                new ContactView("Germany", contactName, address, extractCity(address)),\n                email,\n                sendEmailReminderEnabled,\n                sendReminderMinutesBefore,\n                List.of(),\n                List.of(),\n                List.of(),\n                List.of());\n    }\n\n    public DepartmentView withReferences(\n            List<ScopeReferenceView> scopes,\n            List<ClusterReferenceView> clusters,\n            List<LinkView> links,\n            List<DayoffView> dayoff) {\n        return new DepartmentView(\n                id, name, contact, email, sendEmailReminderEnabled, sendEmailReminderMinutesBefore,\n                scopes, clusters, links, dayoff);\n    }\n\n    public DepartmentView withLessData() {\n        return new DepartmentView(id, name, null, null, null, null, null, null, null, null);\n    }\n\n    private static String extractCity(String address) {\n        if (address == null || address.isBlank()) return null;\n        String[] parts = address.trim().split("\\s+");\n        return parts.length > 0 ? parts[parts.length - 1] : null;\n    }\n}\n',
  },
  "src/main/java/de/muenchen/zms/department/view/OrganisationView.java": {
    language: "java",
    content:
      "package de.muenchen.zms.department.view;\n\nimport com.fasterxml.jackson.annotation.JsonInclude;\nimport java.util.List;\n\n@JsonInclude(JsonInclude.Include.NON_NULL)\npublic record OrganisationView(Long id, String name, List<DepartmentView> departments) {}\n",
  },
  "src/main/java/de/muenchen/zms/department/view/ReferenceViews.java": {
    language: "java",
    content:
      "package de.muenchen.zms.department.view;\n\npublic final class ReferenceViews {\n    private ReferenceViews() {}\n    public record ScopeReferenceView(Long id, String shortName, String ref) {}\n    public record ClusterReferenceView(Long id) {}\n    public record LinkView(String name, String url, boolean target) {}\n    public record DayoffView(long date, String name) {}\n}\n",
  },
  "src/main/java/de/muenchen/zms/department/view/ScopeView.java": {
    language: "java",
    content:
      "package de.muenchen.zms.department.view;\n\nimport com.fasterxml.jackson.annotation.JsonInclude;\n\n@JsonInclude(JsonInclude.Include.NON_NULL)\npublic record ScopeView(Long id, String shortName, ContactView contact) {}\n",
  },
  "src/main/java/de/muenchen/zms/department/view/UseraccountView.java": {
    language: "java",
    content:
      "package de.muenchen.zms.department.view;\n\nimport com.fasterxml.jackson.annotation.JsonInclude;\nimport java.util.List;\n\n@JsonInclude(JsonInclude.Include.NON_NULL)\npublic record UseraccountView(Long id, String name, List<DepartmentView> departments) {}\n",
  },
  "src/main/java/de/muenchen/zms/department/view/WorkstationView.java": {
    language: "java",
    content:
      "package de.muenchen.zms.department.view;\n\nimport com.fasterxml.jackson.annotation.JsonInclude;\n\n@JsonInclude(JsonInclude.Include.NON_NULL)\npublic record WorkstationView(Long id, String name) {}\n",
  },
};

export const defaultPath =
  "src/main/java/de/muenchen/zms/department/api/DepartmentController.java";

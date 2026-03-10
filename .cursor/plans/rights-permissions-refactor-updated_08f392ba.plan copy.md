---
name: rights-permissions-refactor-updated
overview: Refactor ZMS from the legacy numeric rights level model (`Berechtigung`, `RightsLevelManager`, `rights.*`) to an explicit roles + atomic permissions model, updating backend checks, data access, Twig UI, and tests, while keeping rollout/release handling internal.
todos:
  - id: model-permission-list
    content: 1.1 - Consolidate and freeze the atomic permission list from migrations and tickets
    status: pending
  - id: model-role-bundles
    content: 1.1 - Consolidate and freeze the role-to-permission bundles from migrations and tickets
    status: pending
  - id: model-legacy-mapping-table
    content: 1.2 - Write explicit mapping table from legacy numeric Berechtigung levels and rights names to roles/permissions
    status: pending
  - id: model-special-cases
    content: 1.2 - Document handling of special legacy rights like audit and any non-hierarchical flags
    status: pending
  - id: inventory-backend-helpers
    content: 2.1 - Inventory usages of checkRights, hasRights, testRights, and isSuperUser in helpers, entities, and services
    status: pending
  - id: inventory-db-schema
    content: 2.2 - Inventory DB schema and migrations for permission, role, role_permission, user_role, and Berechtigung usage
    status: pending
  - id: inventory-useraccount-query
    content: 2.2 - Inventory all usages of useraccount.Berechtigung and rights__* computed fields in Useraccount queries
    status: pending
  - id: inventory-controllers-statistics-audit
    content: 2.3 - List all controllers/endpoints for statistics and audit that rely on legacy rights
    status: pending
  - id: inventory-controllers-org-scope
    content: 2.3 - List all controllers/endpoints for organisation, department, scope, and availability that use legacy rights
    status: pending
  - id: inventory-controllers-config-mail-dayoff
    content: 2.3 - List all controllers/endpoints for config, mail, dayoff, source, and ticketprinter that use legacy rights
    status: pending
  - id: inventory-controllers-appointments-queues
    content: 2.3 - List all controllers/endpoints for appointments, counters, queues, emergency, and search that use legacy rights
    status: pending
  - id: inventory-controllers-user-misc
    content: 2.3 - List all controllers/endpoints for useraccount, owner, organisation tree, workstation, and misc that use legacy rights
    status: pending
  - id: inventory-twig-navigation
    content: 2.4 - Inventory Twig templates for navigation/global layout using workstation.useraccount.rights or rights.*
    status: pending
  - id: inventory-twig-feature-templates
    content: 2.4 - Inventory feature-specific Twig templates (owner tree, scope forms, queues, statistics, useraccount UI) using legacy rights
    status: pending
  - id: domain-useraccount-defaults
    content: 3.1 - Update Useraccount getDefaults to expose both rights and permissions populated from user_role mappings
    status: pending
  - id: domain-useraccount-schema
    content: 3.1 - Extend zmsentities/schema/useraccount.json to include the new permissions object mirroring the entity structure
    status: pending
  - id: domain-useraccount-accessors
    content: 3.1 - Implement hasPermissions and hasAnyPermission convenience accessors on Useraccount
    status: pending
  - id: domain-superuser-compat
    content: 3.1 - Ensure isSuperUser supports both legacy rights.superuser and permissions.superuser and that superusers receive all permissions flags set to true
    status: pending
  - id: domain-permission-query-classes
    content: 3.2 - Implement Permission, Role, and UserRole query classes to load roles and expand to permissions
    status: pending
  - id: domain-useraccount-mapping-read
    content: 3.2 - Ensure Useraccount entity mapping derives rights from Berechtigung and permissions from new tables on read
    status: pending
  - id: domain-useraccount-mapping-write
    content: 3.2 - Ensure Useraccount reverse mapping writes user_role and keeps Berechtigung consistent using mapping rules
    status: pending
  - id: domain-unified-hasrights
    content: 3.3 - Update Useraccount::hasRights to understand both legacy rights names and new permission names
    status: pending
  - id: domain-new-permission-helper
    content: 3.3 - Optionally introduce dedicated checkPermissions helper wrapping hasPermissions
    status: pending
  - id: controllers-pattern-doc
    content: 4.1 - Document the standard migration pattern for replacing checkRights(oldRight) with permission-based checks or login guards
    status: pending
  - id: controllers-1172-statistics
    content: 4.2 - Refactor statistics controllers in zmsstatistic (Warehouse* etc.) per ticket 1172 to use the statistic permission
    status: pending
  - id: controllers-1172-audit-logs
    content: 4.3 - Refactor audit/log controllers in zmsadmin (ProcessLog etc.) per ticket 1172 to use the logs permission instead of audit
    status: pending
  - id: controllers-1294-org-scope
    content: 4.3 - Refactor organisation, department, scope, availability, and overall calendar controllers per ticket 1294
    status: pending
  - id: controllers-1295-config-mail-dayoff
    content: 4.4 - Refactor config, dayoff, mail, notification, source, and ticketprinter controllers per ticket 1295
    status: pending
  - id: controllers-1296-appointments-queues
    content: 4.5 - Refactor appointments, counters, queues, emergency, search, and workstation controllers per ticket 1296
    status: pending
  - id: controllers-user-owner-tree
    content: 4.6 - Refactor remaining useraccount, owner, and organisation tree controllers still using Berechtigung or legacy rights
    status: pending
  - id: controllers-berechtigung-range-cases
    content: 4.6 - Replace any Berechtigung range based authority checks with explicit roles/permissions
    status: pending
  - id: controllers-tests-statistics
    content: 4.2 - Add or update tests for statistics controllers in zmsstatistic to validate that reporting_viewer has access and audit_viewer does not
    status: pending
  - id: controllers-tests-audit-logs
    content: 4.3 - Add or update tests for audit/log controllers in zmsadmin (customer search logs) to validate that audit_viewer has access and reporting_viewer does not
    status: pending
  - id: controllers-tests-org-scope
    content: 4.3 - Add or update tests for organisation, scope, availability, and overall calendar controllers to validate role-based access
    status: pending
  - id: controllers-tests-config-mail-dayoff
    content: 4.4 - Add or update tests for config, mail, dayoff, and ticketprinter controllers to validate role-based access
    status: pending
  - id: controllers-tests-appointments-queues
    content: 4.5 - Add or update tests for appointments, queues, emergency, and search controllers to validate role-based access
    status: pending
  - id: twig-workstation-model
    content: 5.1 - Extend workstation model to expose useraccount.permissions and useraccount.roles to Twig alongside legacy rights
    status: pending
  - id: twig-navigation-migration
    content: 5.2 - Refactor navigation templates to use permissions/roles instead of rights.*, and verify menu visibility per role
    status: pending
  - id: twig-feature-templates-migration
    content: 5.3 - Refactor feature-specific templates (owner tree, scope/cluster, availability, useraccount, statistics, queues) to use permissions
    status: pending
  - id: twig-queue-table-mapping
    content: 5.3 - Implement explicit queue table mapping from parked/open/waiting/missed/finished/finished-past to corresponding permissions.*
    status: pending
  - id: twig-texts-and-help
    content: 5.4 - Update info/help texts to describe roles and permissions instead of numeric rights
    status: pending
  - id: user-ui-form-submission-roles
    content: 6.1 - Change user create/update forms to submit roles instead of rights arrays
    status: pending
  - id: user-ui-backend-write-roles
    content: 6.1 - Update backend useraccount controllers to write user_role and derive legacy rights/Berechtigung
    status: pending
  - id: user-ui-edit-form-hydration
    content: 6.2 - Hydrate edit forms from user_role to show role selections instead of inferring from Berechtigung
    status: pending
  - id: user-ui-role-widgets
    content: 6.2 - Replace legacy rights checkboxes with role-based widgets and labels sourced from the role table
    status: pending
  - id: user-ui-role-combination-validation
    content: 6.2 - Implement validation for disallowed or risky role combinations where required
    status: pending
  - id: user-ui-role-permission-admin
    content: 7.1 - Implement an admin UI in zmsadmin for managing roles and their permissions that is only visible to and operable by superusers
    status: pending
  - id: tests-integration-role-flows
    content: 7.2 - Write or extend integration tests covering end-to-end flows for each key role
    status: pending
  - id: tests-unit-useraccount-permissions
    content: 7.2 - Add unit tests for Useraccount hasRights, hasPermissions, hasAnyPermission, and isSuperUser
    status: pending
  - id: tests-unit-permission-queries
    content: 7.2 - Add unit tests for Permission, Role, and UserRole query classes
    status: pending
  - id: fixtures-update-user-role
    content: 7.3 - Update fixtures and dev/test seeds to populate user_role and role_permission consistently
    status: pending
  - id: docs-permissions-model
    content: 7.4 - Write developer/admin documentation for the new permissions/roles model and mapping from legacy rights
    status: pending
  - id: docs-legacy-deprecation
    content: 7.4 - Document deprecation timeline and cleanup steps for Berechtigung and legacy rights
    status: pending
  - id: legacy-migration-drop-berechtigung
    content: 8.1 - Implement migration to drop Berechtigung column and related indexes once unused
    status: pending
  - id: legacy-entity-cleanup
    content: 8.2 - Remove legacy rights fields and numeric-level logic from Useraccount and related entities
    status: pending
  - id: legacy-query-cleanup
    content: 8.2 - Remove rights_* fields and Berechtigung-based filters from Useraccount queries and related code
    status: pending
  - id: legacy-schema-cleanup
    content: 8.2 - Remove legacy rights names and structures from zmsentities/schema/useraccount.json so it only describes atomic permissions (and roles if desired)
    status: pending
  - id: legacy-rights-manager-removal
    content: 8.3 - Remove RightsLevelManager helper and all usages
    status: pending
  - id: legacy-feature-flags-cleanup
    content: 8.3 - Remove or simplify any feature flags that only exist for old rights compatibility
    status: pending
  - id: legacy-template-cleanup
    content: 8.4 - Remove any remaining template usages of legacy rights structures
    status: pending
  - id: phpunit-inventory-legacy-tests
    content: 9.1 - Inventory PHPUnit tests that assert behaviour based on Berechtigung or rights.* flags
    status: pending
  - id: phpunit-refactor-to-roles-permissions
    content: 9.1 - Refactor identified legacy tests to assert behaviour in terms of roles and permissions
    status: pending
  - id: phpunit-remove-obsolete-mapping-tests
    content: 9.1 - Remove or simplify PHPUnit tests that only covered obsolete legacy mapping internals
    status: pending
  - id: phpunit-add-edge-case-coverage
    content: 9.2 - Add PHPUnit tests for mixed-role users, superuser overrides, login-only access, and critical deny cases
    status: pending
  - id: phpunit-fixture-cleanup
    content: 9.3 - Remove or update fixtures and test data that only exist for numeric rights scenarios
    status: pending
  - id: phpunit-final-suite-stabilisation
    content: 9.3 - Stabilise the PHPUnit suite after legacy removal and ensure all tests reflect the new model
    status: pending
  - id: ops-run-migrations
    content: 10.1 - Run DB migrations in the zms-web container using vendor/bin/migrate --update and verify schema
    status: pending
  - id: ops-clear-cache
    content: 10.2 - Clear application caches (including cache/@) after migrations so new permissions and Twig changes are picked up
    status: pending
  - id: ops-post-deploy-smoke-tests
    content: 10.3 - Perform post-deploy smoke checks for key roles (system_admin, agent_queue, reporting_viewer, audit_viewer) on staging/production
    status: pending
isProject: false
---

## Rights & Permissions Refactor Plan (Updated)

### 1. Clarify target model and mapping

- **1.1 Document atomic permissions and roles**
  - Consolidate the target permission list (appointment, availability, calldisplay, cherrypick, cluster, config, counter, customersearch, dayoff, department, emergency, finishedqueue, finishedqueuepast, logs, mailtemplates, missedqueue, openqueue, organisation, overviewcalendar, parkedqueue, restrictedscope, scope, source, statistic, ticketprinter, useraccount, waitingqueue, superuser) in one design doc referencing `[zmsdb/migrations/91771408741-create-permissions-table.sql](zmsdb/migrations/91771408741-create-permissions-table.sql)`.
  - Consolidate the canonical role list and descriptions from `[zmsdb/migrations/91771408752-create-roles-table.sql](zmsdb/migrations/91771408752-create-roles-table.sql)`.
  - Consolidate the role → permission bundles (agent_basic, agent_queue, agent_queue_plus, appointment_admin, reporting_viewer, user_admin, audit_viewer, system_admin) from `[zmsdb/migrations/91771408763-create-role-permission-table.sql](zmsdb/migrations/91771408763-create-role-permission-table.sql)` and the table in the description.
  - Document the `user_role` bridge table and its foreign keys based on `[zmsdb/migrations/91771408773-create-user-role-table.sql](zmsdb/migrations/91771408773-create-user-role-table.sql)` and the follow-up constraint fix in `[zmsdb/migrations/91773136364-alter-user-role-fk.sql](zmsdb/migrations/91773136364-alter-user-role-fk.sql)`.
  - Include the historical user migration from `[zmsdb/migrations/91771576480-migrate-users-to-new-roles.sql](zmsdb/migrations/91771576480-migrate-users-to-new-roles.sql)` as the baseline mapping from legacy `Berechtigung` levels to initial roles.
- **1.2 Define legacy → new mapping rules**
  - Freeze the mapping from old numeric `Berechtigung` values and legacy right names (`basic`, `scope`, `audit`, `useraccount`, etc.) to new roles and permissions.
  - Explicitly specify how `audit` and any other “special” non-hierarchical rights map into the new world.

### 2. Inventory current usage (backend, DB, frontend)

- **2.1 Backend rights API & helpers**
  - Confirm entry points and responsibilities of `checkRights()`, `hasRights()`, `testRights()`, and `isSuperUser()` in `[zmsapi/src/Zmsapi/Helper/User.php](zmsapi/src/Zmsapi/Helper/User.php)` and `[zmsentities/src/Zmsentities/Useraccount.php](zmsentities/src/Zmsentities/Useraccount.php)`.
  - List all non-controller usages where these methods influence domain logic (e.g. `Scope::hasAccess()`, statistic helpers, collections that change output based on rights).
- **2.2 DB and query layer**
  - Catalogue all usages of `useraccount.Berechtigung` and all computed `rights__*` fields in `[zmsdb/src/Zmsdb/Query/Useraccount.php](zmsdb/src/Zmsdb/Query/Useraccount.php)` and related queries (e.g. `ExchangeUseraccount`).
  - Catalogue migrations and schema for `permission`, `role`, `role_permission`, `user_role` and the existing `91771576480-migrate-users-to-new-roles.sql` logic.
- **2.3 Controllers / endpoints**
  - Generate a checklist of all controllers calling `checkRights()` or using rights-related methods, grouped roughly by domain (statistics/audit; organisation/department/scope/availability; configuration/mail/dayoff/source; appointments/counter/queue/emergency; useraccount/owner/workstation; ticketprinter/calldisplay).
  - For each group, record the current right(s) checked and the desired new permission(s), using the mapping tables you already drafted in the tickets (1172/1294/1295/1296) as the initial source of truth.
- **2.4 Twig templates / frontend**
  - List all templates using `workstation.useraccount.rights.*` or `rights.*` (e.g. navigation, owner/organisation tree, scope/cluster forms, useraccount forms & lists, status/config info, statistic navigation, queue tables).
  - For each usage, record whether it should become:
    - a direct permission flag (e.g. `permissions.statistic`),
    - a role-based check (e.g. `hasRole('appointment_admin')`), or
    - derived from multiple permissions.

### 3. Strengthen domain model for permissions and roles (backwards compatible)

- **3.1 Extend `Useraccount` entity to expose permissions cleanly**
  - Extend `[zmsentities/schema/useraccount.json](zmsentities/schema/useraccount.json)` to define a `permissions` object that mirrors the atomic permissions exposed by the entity.
  - In `[zmsentities/src/Zmsentities/Useraccount.php](zmsentities/src/Zmsentities/Useraccount.php)`, make sure `getDefaults()` includes both `rights` (legacy names) and `permissions` (atomic names), with `permissions` populated from `user_role → role_permission → permission` for non-superusers.
  - Implement convenience accessors:
    - `hasPermissions(array $permissions)` (all-of semantics, mirroring `hasRights()`),
    - `hasAnyPermission(array $permissions)` if you foresee needing any-of checks.
  - Ensure `isSuperUser()` returns true if either the legacy `rights.superuser` or `permissions.superuser` is set, and hydrate superusers such that all permission flags in the `permissions` array are `true`.
- **3.2 Make query layer able to hydrate permissions**
  - Add or complete query classes for `Permission`, `Role`, and `UserRole` in `zmsdb/src/Zmsdb/Query` to:
    - Load a user’s roles and expand them into permissions.
    - Provide simple APIs like `UseraccountQuery::withPermissions()` / `::withRoles()`.
  - Ensure referential integrity for role assignments, including the `user_role.user_id` foreign key cascade semantics defined in `[zmsdb/migrations/91773136364-alter-user-role-fk.sql](zmsdb/migrations/91773136364-alter-user-role-fk.sql)`, so deleting a user also cleans up its role mappings.
  - In `Useraccount`’s `getEntityMapping()` / `reverseEntityMapping()`, ensure:
    - Reading: rights are still derived from `Berechtigung` as today, and `permissions` are derived from the new tables.
    - Writing: updates continue to keep `Berechtigung` and `user_role` consistent (using the mapping of 1170), as described in ZMSKVR-1171.
- **3.3 Unify backend checks on a single abstraction (without breaking callers yet)**
  - Update `Useraccount::hasRights()` to:
    - First interpret arguments that match new permission names by consulting `permissions`.
    - Fallback to legacy `rights` for old names, so `checkRights('scope')` still works but `checkRights('statistic')` is also accepted.
  - Optionally introduce new helper(s) (e.g. `checkPermissions()` as a thin wrapper) while keeping `checkRights()` as the public API during migration.

### 4. Controller refactor to atomic permissions (by domain)

- **4.1 General migration pattern for each controller**
  - For each controller using `checkRights('oldRightName')`, decide the new permission(s) from the mapping table.
  - Change the check to use the new permission(s) by name, relying on the updated `hasRights()` to understand both legacy and atomic names.
  - Where the current check is ambiguous (“or only login check?”), explicitly decide whether:
    - a dedicated permission is required, or
    - any authenticated user should be allowed (replace `checkRights()` with a dedicated login/auth guard).
  - Add or adjust unit/functional tests for each controller to assert that roles with/without the intended permission behave correctly.
  - Maintain an explicit checklist of all backend `checkRights()` usages (currently ~115 occurrences across ~114 `.php` files as per grep) and mark each entry as migrated (old right → new permission or login-only), reviewed, and tested, grouped by the domain subsections 4.2–4.6. Seed the checklist with:
    - `[zmsapi/src/Zmsapi/ScopePreferedByCluster.php](zmsapi/src/Zmsapi/ScopePreferedByCluster.php)`
    - `[zmsapi/src/Zmsapi/DepartmentDelete.php](zmsapi/src/Zmsapi/DepartmentDelete.php)`
    - `[zmsapi/src/Zmsapi/WorkstationProcessDelete.php](zmsapi/src/Zmsapi/WorkstationProcessDelete.php)`
    - `[zmsapi/src/Zmsapi/WorkstationUpdate.php](zmsapi/src/Zmsapi/WorkstationUpdate.php)`
    - `[zmsapi/src/Zmsapi/WorkstationGet.php](zmsapi/src/Zmsapi/WorkstationGet.php)`
    - `[zmsapi/src/Zmsapi/AvailabilityClosureToggle.php](zmsapi/src/Zmsapi/AvailabilityClosureToggle.php)`
    - `[zmsapi/src/Zmsapi/ConfigGet.php](zmsapi/src/Zmsapi/ConfigGet.php)`
    - `[zmsapi/src/Zmsapi/ScopeWithWorkstationCount.php](zmsapi/src/Zmsapi/ScopeWithWorkstationCount.php)`
    - `[zmsapi/src/Zmsapi/DepartmentGet.php](zmsapi/src/Zmsapi/DepartmentGet.php)`
    - `[zmsapi/src/Zmsapi/ConfigUpdate.php](zmsapi/src/Zmsapi/ConfigUpdate.php)`
    - `[zmsapi/src/Zmsapi/MailDelete.php](zmsapi/src/Zmsapi/MailDelete.php)`
    - `[zmsapi/src/Zmsapi/OverallCalendarRead.php](zmsapi/src/Zmsapi/OverallCalendarRead.php)`
    - `[zmsapi/src/Zmsapi/ScopeDelete.php](zmsapi/src/Zmsapi/ScopeDelete.php)`
    - `[zmsapi/src/Zmsapi/UseraccountUpdate.php](zmsapi/src/Zmsapi/UseraccountUpdate.php)`
    - `[zmsapi/src/Zmsapi/WorkstationProcessGet.php](zmsapi/src/Zmsapi/WorkstationProcessGet.php)`
    - `[zmsapi/src/Zmsapi/Helper/User.php](zmsapi/src/Zmsapi/Helper/User.php)`
    - `[zmsapi/src/Zmsapi/DepartmentAddCluster.php](zmsapi/src/Zmsapi/DepartmentAddCluster.php)`
    - `[zmsapi/src/Zmsapi/AvailabilityDelete.php](zmsapi/src/Zmsapi/AvailabilityDelete.php)`
    - `[zmsapi/src/Zmsapi/DepartmentUpdate.php](zmsapi/src/Zmsapi/DepartmentUpdate.php)`
    - `[zmsapi/src/Zmsapi/NotificationDelete.php](zmsapi/src/Zmsapi/NotificationDelete.php)`
    - `[zmsapi/src/Zmsapi/UseraccountGet.php](zmsapi/src/Zmsapi/UseraccountGet.php)`
    - `[zmsapi/src/Zmsapi/UseraccountListByDepartments.php](zmsapi/src/Zmsapi/UseraccountListByDepartments.php)`
    - `[zmsapi/src/Zmsapi/ScopeGet.php](zmsapi/src/Zmsapi/ScopeGet.php)`
    - `[zmsapi/src/Zmsapi/WarehouseSubjectGet.php](zmsapi/src/Zmsapi/WarehouseSubjectGet.php)`
    - `[zmsapi/src/Zmsapi/ProcessListByClusterAndDate.php](zmsapi/src/Zmsapi/ProcessListByClusterAndDate.php)`
    - `[zmsapi/src/Zmsapi/DayoffUpdate.php](zmsapi/src/Zmsapi/DayoffUpdate.php)`
    - `[zmsapi/src/Zmsapi/NotificationAdd.php](zmsapi/src/Zmsapi/NotificationAdd.php)`
    - `[zmsapi/src/Zmsapi/ProcessFreeUnique.php](zmsapi/src/Zmsapi/ProcessFreeUnique.php)`
    - `[zmsapi/src/Zmsapi/UserQueue.php](zmsapi/src/Zmsapi/UserQueue.php)`
    - `[zmsapi/src/Zmsapi/CalendarGet.php](zmsapi/src/Zmsapi/CalendarGet.php)`
    - `[zmsapi/src/Zmsapi/ProcessListByExternalUserId.php](zmsapi/src/Zmsapi/ProcessListByExternalUserId.php)`
    - `[zmsapi/src/Zmsapi/UseraccountListByRole.php](zmsapi/src/Zmsapi/UseraccountListByRole.php)`
    - `[zmsapi/src/Zmsapi/DepartmentByScopeId.php](zmsapi/src/Zmsapi/DepartmentByScopeId.php)`
    - `[zmsapi/src/Zmsapi/ProcessNextByScope.php](zmsapi/src/Zmsapi/ProcessNextByScope.php)`
    - `[zmsapi/src/Zmsapi/ClusterCalldisplayImageDataUpdate.php](zmsapi/src/Zmsapi/ClusterCalldisplayImageDataUpdate.php)`
    - `[zmsapi/src/Zmsapi/WorkstationProcess.php](zmsapi/src/Zmsapi/WorkstationProcess.php)`
    - `[zmsapi/src/Zmsapi/ConflictListByScope.php](zmsapi/src/Zmsapi/ConflictListByScope.php)`
    - `[zmsapi/src/Zmsapi/ScopeEmergency.php](zmsapi/src/Zmsapi/ScopeEmergency.php)`
    - `[zmsapi/src/Zmsapi/SourceUpdate.php](zmsapi/src/Zmsapi/SourceUpdate.php)`
    - `[zmsapi/src/Zmsapi/OrganisationUpdate.php](zmsapi/src/Zmsapi/OrganisationUpdate.php)`
    - `[zmsapi/src/Zmsapi/NotificationList.php](zmsapi/src/Zmsapi/NotificationList.php)`
    - `[zmsapi/src/Zmsapi/OwnerDelete.php](zmsapi/src/Zmsapi/OwnerDelete.php)`
    - `[zmsapi/src/Zmsapi/MailGet.php](zmsapi/src/Zmsapi/MailGet.php)`
    - `[zmsapi/src/Zmsapi/WarehousePeriodGet.php](zmsapi/src/Zmsapi/WarehousePeriodGet.php)`
    - `[zmsapi/src/Zmsapi/ScopeListByCluster.php](zmsapi/src/Zmsapi/ScopeListByCluster.php)`
    - `[zmsapi/src/Zmsapi/OwnerList.php](zmsapi/src/Zmsapi/OwnerList.php)`
    - `[zmsapi/src/Zmsapi/UseraccountAdd.php](zmsapi/src/Zmsapi/UseraccountAdd.php)`
    - `[zmsapi/src/Zmsapi/ClusterQueue.php](zmsapi/src/Zmsapi/ClusterQueue.php)`
    - `[zmsapi/src/Zmsapi/OwnerGet.php](zmsapi/src/Zmsapi/OwnerGet.php)`
    - `[zmsapi/src/Zmsapi/ProcessListByScopeAndStatus.php](zmsapi/src/Zmsapi/ProcessListByScopeAndStatus.php)`
    - `[zmsapi/src/Zmsapi/UseraccountListByRoleAndDepartments.php](zmsapi/src/Zmsapi/UseraccountListByRoleAndDepartments.php)`
    - `[zmsapi/src/Zmsapi/ClusterGet.php](zmsapi/src/Zmsapi/ClusterGet.php)`
    - `[zmsapi/src/Zmsapi/ClusterUpdate.php](zmsapi/src/Zmsapi/ClusterUpdate.php)`
    - `[zmsapi/src/Zmsapi/MailTemplatesDelete.php](zmsapi/src/Zmsapi/MailTemplatesDelete.php)`
    - `[zmsapi/src/Zmsapi/ProcessListByScopeAndDate.php](zmsapi/src/Zmsapi/ProcessListByScopeAndDate.php)`
    - `[zmsapi/src/Zmsapi/UseraccountDelete.php](zmsapi/src/Zmsapi/UseraccountDelete.php)`
    - `[zmsapi/src/Zmsapi/DepartmentList.php](zmsapi/src/Zmsapi/DepartmentList.php)`
    - `[zmsapi/src/Zmsapi/AppointmentUpdate.php](zmsapi/src/Zmsapi/AppointmentUpdate.php)`
    - `[zmsapi/src/Zmsapi/MailList.php](zmsapi/src/Zmsapi/MailList.php)`
    - `[zmsapi/src/Zmsapi/ProcessAddLog.php](zmsapi/src/Zmsapi/ProcessAddLog.php)`
    - `[zmsapi/src/Zmsapi/OrganisationList.php](zmsapi/src/Zmsapi/OrganisationList.php)`
    - `[zmsapi/src/Zmsapi/ProcessDeleteQuick.php](zmsapi/src/Zmsapi/ProcessDeleteQuick.php)`
    - `[zmsapi/src/Zmsapi/ProcessReserve.php](zmsapi/src/Zmsapi/ProcessReserve.php)`
    - `[zmsapi/src/Zmsapi/UseraccountList.php](zmsapi/src/Zmsapi/UseraccountList.php)`
    - `[zmsapi/src/Zmsapi/OrganisationByDepartment.php](zmsapi/src/Zmsapi/OrganisationByDepartment.php)`
    - `[zmsapi/src/Zmsapi/DepartmentWorkstationList.php](zmsapi/src/Zmsapi/DepartmentWorkstationList.php)`
    - `[zmsapi/src/Zmsapi/MailTemplatesPreview.php](zmsapi/src/Zmsapi/MailTemplatesPreview.php)`
    - `[zmsapi/src/Zmsapi/MailAdd.php](zmsapi/src/Zmsapi/MailAdd.php)`
    - `[zmsapi/src/Zmsapi/ScopeCalldisplayImageDataDelete.php](zmsapi/src/Zmsapi/ScopeCalldisplayImageDataDelete.php)`
    - `[zmsapi/src/Zmsapi/ProcessReservedList.php](zmsapi/src/Zmsapi/ProcessReservedList.php)`
    - `[zmsapi/src/Zmsapi/AvailabilityListUpdate.php](zmsapi/src/Zmsapi/AvailabilityListUpdate.php)`
    - `[zmsapi/src/Zmsapi/ProcessFree.php](zmsapi/src/Zmsapi/ProcessFree.php)`
    - `[zmsapi/src/Zmsapi/WarehousePeriodListGet.php](zmsapi/src/Zmsapi/WarehousePeriodListGet.php)`
    - `[zmsapi/src/Zmsapi/WorkstationDelete.php](zmsapi/src/Zmsapi/WorkstationDelete.php)`
    - `[zmsapi/src/Zmsapi/WorkstationProcessWaitingnumber.php](zmsapi/src/Zmsapi/WorkstationProcessWaitingnumber.php)`
    - `[zmsapi/src/Zmsapi/MailTemplatesUpdate.php](zmsapi/src/Zmsapi/MailTemplatesUpdate.php)`
    - `[zmsapi/src/Zmsapi/ProcessRedirect.php](zmsapi/src/Zmsapi/ProcessRedirect.php)`
    - `[zmsapi/src/Zmsapi/DayoffList.php](zmsapi/src/Zmsapi/DayoffList.php)`
    - `[zmsapi/src/Zmsapi/OrganisationGet.php](zmsapi/src/Zmsapi/OrganisationGet.php)`
    - `[zmsapi/src/Zmsapi/AvailabilityListByScope.php](zmsapi/src/Zmsapi/AvailabilityListByScope.php)`
    - `[zmsapi/src/Zmsapi/ProcessNextByCluster.php](zmsapi/src/Zmsapi/ProcessNextByCluster.php)`
    - `[zmsapi/src/Zmsapi/OrganisationAddDepartment.php](zmsapi/src/Zmsapi/OrganisationAddDepartment.php)`
    - `[zmsapi/src/Zmsapi/ClusterDelete.php](zmsapi/src/Zmsapi/ClusterDelete.php)`
    - `[zmsapi/src/Zmsapi/MailCustomTemplatesGet.php](zmsapi/src/Zmsapi/MailCustomTemplatesGet.php)`
    - `[zmsapi/src/Zmsapi/ScopeQueue.php](zmsapi/src/Zmsapi/ScopeQueue.php)`
    - `[zmsapi/src/Zmsapi/ScopeUpdate.php](zmsapi/src/Zmsapi/ScopeUpdate.php)`
    - `[zmsapi/src/Zmsapi/OwnerAddOrganisation.php](zmsapi/src/Zmsapi/OwnerAddOrganisation.php)`
    - `[zmsapi/src/Zmsapi/ClusterWithWorkstationCount.php](zmsapi/src/Zmsapi/ClusterWithWorkstationCount.php)`
    - `[zmsapi/src/Zmsapi/MailTemplatesGet.php](zmsapi/src/Zmsapi/MailTemplatesGet.php)`
    - `[zmsapi/src/Zmsapi/CounterGhostWorkstation.php](zmsapi/src/Zmsapi/CounterGhostWorkstation.php)`
    - `[zmsapi/src/Zmsapi/ProcessFinished.php](zmsapi/src/Zmsapi/ProcessFinished.php)`
    - `[zmsapi/src/Zmsapi/MailTemplatesCreateCustomization.php](zmsapi/src/Zmsapi/MailTemplatesCreateCustomization.php)`
    - `[zmsapi/src/Zmsapi/OrganisationByScope.php](zmsapi/src/Zmsapi/OrganisationByScope.php)`
    - `[zmsapi/src/Zmsapi/OwnerByOrganisation.php](zmsapi/src/Zmsapi/OwnerByOrganisation.php)`
    - `[zmsapi/src/Zmsapi/ScopeEmergencyStop.php](zmsapi/src/Zmsapi/ScopeEmergencyStop.php)`
    - `[zmsapi/src/Zmsapi/AvailabilitySlotsUpdate.php](zmsapi/src/Zmsapi/AvailabilitySlotsUpdate.php)`
    - `[zmsapi/src/Zmsapi/WorkstationPassword.php](zmsapi/src/Zmsapi/WorkstationPassword.php)`
    - `[zmsapi/src/Zmsapi/OrganisationDelete.php](zmsapi/src/Zmsapi/OrganisationDelete.php)`
    - `[zmsapi/src/Zmsapi/ProcessLog.php](zmsapi/src/Zmsapi/ProcessLog.php)`
    - `[zmsapi/src/Zmsapi/ProcessQueued.php](zmsapi/src/Zmsapi/ProcessQueued.php)`
    - `[zmsapi/src/Zmsapi/ScopeCalldisplayImageDataUpdate.php](zmsapi/src/Zmsapi/ScopeCalldisplayImageDataUpdate.php)`
    - `[zmsapi/src/Zmsapi/AvailabilityGet.php](zmsapi/src/Zmsapi/AvailabilityGet.php)`
    - `[zmsapi/src/Zmsapi/TicketprinterListByScopeList.php](zmsapi/src/Zmsapi/TicketprinterListByScopeList.php)`
    - `[zmsapi/src/Zmsapi/ProcessSearch.php](zmsapi/src/Zmsapi/ProcessSearch.php)`
    - `[zmsapi/src/Zmsapi/ClusterCalldisplayImageDataDelete.php](zmsapi/src/Zmsapi/ClusterCalldisplayImageDataDelete.php)`
    - `[zmsapi/src/Zmsapi/DepartmentAddScope.php](zmsapi/src/Zmsapi/DepartmentAddScope.php)`
    - `[zmsapi/src/Zmsapi/WarehouseSubjectListGet.php](zmsapi/src/Zmsapi/WarehouseSubjectListGet.php)`
    - `[zmsapi/src/Zmsapi/OrganisationByCluster.php](zmsapi/src/Zmsapi/OrganisationByCluster.php)`
    - `[zmsapi/src/Zmsapi/OwnerUpdate.php](zmsapi/src/Zmsapi/OwnerUpdate.php)`
    - `[zmsapi/src/Zmsapi/ScopeList.php](zmsapi/src/Zmsapi/ScopeList.php)`
    - `[zmsapi/src/Zmsapi/AvailabilityClosureRead.php](zmsapi/src/Zmsapi/AvailabilityClosureRead.php)`
    - `[zmsapi/src/Zmsapi/WorkstationProcessParked.php](zmsapi/src/Zmsapi/WorkstationProcessParked.php)`
    - `[zmsapi/src/Zmsapi/ClusterByScopeId.php](zmsapi/src/Zmsapi/ClusterByScopeId.php)`
    - `[zmsapi/src/Zmsapi/ScopeEmergencyRespond.php](zmsapi/src/Zmsapi/ScopeEmergencyRespond.php)`
- **4.2 Statistics (ZMSKVR-1172)**
  - Treat statistics as a dedicated concern in `zmsstatistic` guarded by the `statistic` permission.
  - Update `Warehouse*` controllers (zmsstatistic) to call `checkRights('statistic')` instead of `checkRights('scope')`.
  - Verify via tests and, if available, manual scenarios that:
    - `reporting_viewer` can access statistics endpoints in `zmsstatistic` but cannot see audit logs in customer search in zmsadmin.
- **4.3 Audit / logs in customer search (ZMSKVR-1172)**
  - Treat audit/log viewing in customer search as a separate concern in `zmsadmin` guarded by the `logs` permission.
  - Update `ProcessLog` (zmsadmin) to call `checkRights('logs')` instead of `checkRights('audit')`.
  - Verify via tests and, if available, manual scenarios that:
    - `audit_viewer` can see logs in customer search in `zmsadmin` but does not gain access to zmsstatistic endpoints.
- **4.3 Organisation, departments, scopes, availability, overall calendar (ZMSKVR-1294)**
  - For each controller in the 1294 table, apply the planned replacements (e.g. `AvailabilityClosureRead` → `availability`, `OverallCalendarRead` → `overviewcalendar`, `ConflictListByScope` → `availability`).
  - Decide the “or only login?” cases explicitly and document them:
    - For endpoints that only read non-sensitive metadata, consider switching to a login check.
    - For configuration-changing endpoints, always require a permission (`availability`, `organisation`, `scope`, etc.).
  - Ensure `agent_queue_plus` and `appointment_admin` have the necessary permissions to:
    - open the overall calendar,
    - manage availabilities as per the acceptance criteria.
- **4.4 Day off, mail, notifications, source, configuration, ticketprinter (ZMSKVR-1295)**
  - Update each controller as per the 1295 mapping table (e.g. `ConfigUpdate` → `config`, `Dayoff*` → `dayoff`, mail/newsletter endpoints → `appointment` or `mailtemplates`, `SourceUpdate` → `source`, `TicketprinterListByScopeList` → `ticketprinter` or login).
  - For `ConfigGet`, decide between `config` and `counter` and encode that decision consistently across UI expectations and docs.
  - Verify via tests that only `system_admin` (or whichever roles you designate) can:
    - manage mail templates,
    - manage day off, sources, ticketprinter configuration, and system configuration.
- **4.5 Appointments, counters, queues, emergency, search (ZMSKVR-1296)**
  - Apply the mapping table systematically across all controllers (appointments, counter calendar, cluster/department/scope lookups, process operations, queue endpoints, emergency scope calls, workstation CRUD).
  - Decide for each “or only login?” case whether to require a specific permission (e.g. `appointment`, `counter`, `emergency`, `customersearch`) or just authentication.
  - Update or add tests validating:
    - `agent_basic` cannot see any queue.
    - `agent_queue` can see all queues and perform queue operations.
    - `agent_queue_plus` has the additional `overviewcalendar`/statistics-related access as intended.
- **4.6 Useraccount, owner, organisation tree, workstation, ticketprinter, misc.**
  - Migrate remaining controllers outside the tickets that still use legacy rights names or `Berechtigung`-derived behaviour (e.g. `Useraccount*`, `Owner*`, certain `Scope*` and `Cluster*` APIs, `UseraccountListByRole`).
  - For list/connectivity endpoints that currently derive authority from `Berechtigung` ranges, translate that logic to explicit permissions and/or roles using the new schema.

### 5. Twig / frontend migration to new permissions

- **5.1 Introduce a frontend-friendly permission/role API on `workstation`**
  - Extend the workstation model so that Twig templates receive both:
    - a `permissions` structure with atomic flags (e.g. `workstation.useraccount.permissions.statistic`), and
    - optionally a `roles` list (e.g. `workstation.useraccount.roles`), so templates can use whichever is clearer.
  - Keep the legacy `rights` structure populated during migration for compatibility.
- **5.2 Refactor navigation and global layout templates**
  - In `zmsadmin/templates/block/navigation/navigation.twig` and related files, gradually replace checks like `workstation.useraccount.rights.superuser` or `rights.scope` with `permissions.superuser`, `permissions.scope`, or role-based checks.
  - Verify that menu entries appear/disappear correctly for each role (agent_basic, agent_queue, agent_queue_plus, appointment_admin, reporting_viewer, user_admin, audit_viewer, system_admin).
- **5.3 Refactor feature-specific templates**
  - For owner/organisation tree, scope/cluster forms, availability/overall calendar, useraccount management, status/config info, statistic navigation, and queue tables:
    - Replace `rights.`* conditions with the matching permission flags from the target mapping.
    - Maintain a checklist of all Twig templates and blocks that reference `useraccount.rights` / `rights.`* (currently ~64 results across ~20 `.twig` files as per grep), and for each entry record the old condition, the new permission/role-based condition, and whether it has been visually/functionally verified. Seed the checklist with:
      - `[zmsadmin/templates/block/useraccount/profile/changepassword.twig](zmsadmin/templates/block/useraccount/profile/changepassword.twig)`
      - `[zmsadmin/templates/block/useraccount/info.twig](zmsadmin/templates/block/useraccount/info.twig)`
      - `[zmsstatistic/templates/block/navigation/navigation.twig](zmsstatistic/templates/block/navigation/navigation.twig)`
      - `[zmsadmin/templates/page/configinfo.twig](zmsadmin/templates/page/configinfo.twig)`
      - `[zmsadmin/templates/block/notification/form.twig](zmsadmin/templates/block/notification/form.twig)`
      - `[zmsadmin/templates/block/useraccount/listByDepartment.twig](zmsadmin/templates/block/useraccount/listByDepartment.twig)`
      - `[zmsadmin/templates/page/status.twig](zmsadmin/templates/page/status.twig)`
      - `[zmsadmin/templates/block/useraccount/edit.twig](zmsadmin/templates/block/useraccount/edit.twig)`
      - `[zmsadmin/templates/block/page/configinfo.twig](zmsadmin/templates/block/page/configinfo.twig)`
      - `[zmsadmin/templates/block/calendar/calendarMonth.twig](zmsadmin/templates/block/calendar/calendarMonth.twig)`
      - `[zmsadmin/templates/block/cluster/scopeSelect.twig](zmsadmin/templates/block/cluster/scopeSelect.twig)`
      - `[zmsstatistic/templates/block/statistic/departmentlist.twig](zmsstatistic/templates/block/statistic/departmentlist.twig)`
      - `[zmsadmin/templates/block/navigation/navigation.twig](zmsadmin/templates/block/navigation/navigation.twig)`
      - `[zmsadmin/templates/block/owner/overview.twig](zmsadmin/templates/block/owner/overview.twig)`
      - `[zmsadmin/templates/block/organisation/form.twig](zmsadmin/templates/block/organisation/form.twig)`
      - `[zmsadmin/templates/block/scope/form.twig](zmsadmin/templates/block/scope/form.twig)`
      - `[zmsadmin/templates/block/cluster/form.twig](zmsadmin/templates/block/cluster/form.twig)`
      - `[zmsadmin/templates/block/useraccount/departmentlist.twig](zmsadmin/templates/block/useraccount/departmentlist.twig)`
      - `[zmsadmin/templates/block/appointment/times.twig](zmsadmin/templates/block/appointment/times.twig)`
      - `[zmsadmin/templates/block/useraccount/list.twig](zmsadmin/templates/block/useraccount/list.twig)`
    - For queue table (`block/queue/table.twig`), explicitly implement the mapping:
      - parked queue → `permissions.parkedqueue`
      - open/in-progress → `permissions.openqueue`
      - waiting queue → `permissions.waitingqueue`
      - missed appointments → `permissions.missedqueue`
      - finished appointments (current day/range) → `permissions.finishedqueue`
      - finished appointments (historical/past) → `permissions.finishedqueuepast`
- **5.4 Update UI texts and help/info blocks**
  - In templates like `block/useraccount/info.twig`, switch the explanation from legacy right names to the new roles/permissions vocabulary, while preserving user-understandable German wording.

### 6. User management UI and flows (Phase 3 from description)

- Building on the controller and Twig refactors in sections 4 and 5, this section focuses on verifying and finalising the end-to-end useraccount CRUD flows (backend mapping + zmsadmin UI) under the new roles/permissions model.
- **6.1 Switch user creation/update to write roles instead of numeric/legacy rights**
  - In all relevant controllers (`UseraccountAdd`, `UseraccountUpdate`, etc.), change the write path so that:
    - The form submits roles (e.g. `agent_queue`, `appointment_admin`) instead of a `rights` array used to calculate `Berechtigung`.
    - The backend writes these roles into `user_role` and derives legacy `rights` (and `Berechtigung`, while still needed) from the new assignments.
  - Implement the reverse mapping for edit forms: hydrate role selections from `user_role` and show them in the UI, instead of inferring from `Berechtigung`.
- **6.2 Update role selection widgets and labels**
  - In `block/useraccount/edit.twig` and related UI, replace legacy checkboxes and numeric level hints with the new role names and descriptions from the `role` table.
  - Ensure that role combinations are validated where necessary (e.g. if you want to forbid certain mixes like both audit_viewer and agent_basic in some contexts).
- **6.3 Add management UI for roles and permissions (optional but recommended)**
  - Create a new admin section in `zmsadmin` (navigation entry + controller + Twig screens) to CRUD roles, assign permissions to roles, and (if allowed) manage the set of permissions.
  - Ensure that:
    - the navigation entry is only rendered for superusers,
    - the UI is only accessible to superusers on the backend (guarded by the `superuser` permission or equivalent),
    - all CRUD operations in this area are restricted to superusers.

### 7. Testing, fixtures, and documentation

- **7.1 Add unit tests for the new ui and backend section for the CRUD operations in zmsadmin zmsapi and maybe zmsdb**
  - Introduce or extend unit tests that:
    - Exercise key flows for each role (agent_basic, agent_queue, agent_queue_plus, appointment_admin, reporting_viewer, user_admin, audit_viewer, system_admin).
    - Assert that each endpoint’s permission checks match the intended matrix.
    - Explicitly verify that `agent_basic`, `agent_queue`, and `agent_queue_plus` can all trigger and respond to emergency flows and perform full appointment CRUD where intended.
  - Add unit tests for `Useraccount::hasRights()`, `hasPermissions()`, `isSuperUser()`, and query classes dealing with `user_role`/`role_permission`.
- **7.2 Fixtures and local dev data**
  - Update dev/test seeds so that they populate `user_role` and `role_permission` consistently and no longer rely on magic numeric `Berechtigung` levels alone.
- **7.3 Developer and admin documentation**
  - Document the new model (permissions, roles, mapping from old values), how to grant rights, and how to debug access problems.
  - Clearly mark the legacy structures as deprecated with a timeline for removal.

### 8. Remove legacy numeric rights model and helpers (ZMSKVR-1173)

- **8.1 DB schema cleanup**
  - Add a migration that:
    - Drops the `Berechtigung` column from `nutzer` once all code is confirmed to no longer use it.
    - Drops associated indexes (e.g. `idx_nutzer_berechtigung`).
- **8.2 Entity and query cleanup**
  - In `Useraccount` and related entities:
    - Remove computed legacy rights (`basic`, `scope`, `sms`, `audit`, etc.) from `rights`.
    - Simplify `getDefaults()` and `getEntityMapping()` so they only expose atomic permissions (and roles if desired).
    - Remove `getRightsLevel()` and any numeric-level based logic.
  - In `Zmsdb/Query/Useraccount.php` and other queries:
    - Remove `rights`__* fields based on `Berechtigung` expressions.
    - Remove any remaining references to `Berechtigung` in filters like `addConditionRoleLevel()`; replace them with role or permission-based filters.
  - In `[zmsentities/schema/useraccount.json](zmsentities/schema/useraccount.json)`:
    - Remove legacy right names and structures so the schema only defines atomic permissions (and, if you keep it there, roles).
- **8.3 Helper and compatibility code removal**
  - Delete `Helper/RightsLevelManager.php` and all usages (`RightsLevelManager::getLevel()`, `$possibleRights`).
  - Remove any feature-flag-based compatibility shims that only exist for the old model (keeping `RIGHTSCHECK_ENABLED` if you still want a global on/off, but no longer using it for dual behaviour).
- **8.4 Final UI and template cleanup**
  - Ensure all templates now rely exclusively on atomic permission/role data structures.
  - Remove any dead code paths that still expect numeric rights or old names.

### 9. Final PHPUnit test cleanup and alignment

- **9.1 Align existing PHPUnit tests with the new model**
  - Identify PHPUnit test classes and methods that assert behaviour based on numeric `Berechtigung` levels or legacy `rights.`* flags.
  - Rewrite these tests to express expectations in terms of roles and atomic permissions (e.g. `agent_queue_plus` + `overviewcalendar`, `system_admin` + `config`).
  - Remove or simplify tests that only validated legacy mapping internals which are no longer relevant once the new schema is the single source of truth.
- **9.2 Add missing PHPUnit coverage for edge cases**
  - Add tests that cover edge cases such as mixed-role users, superuser overrides, and endpoints that deliberately allow “login only” access.
  - Ensure tests explicitly cover failure cases (403/denied) when a role lacks a given permission, especially for critical domains (statistics, audit logs, configuration).
- **9.3 Stabilise the test suite after legacy removal**
  - After step 8, run the full PHPUnit suite and clean up any remaining failing or obsolete tests tied to `Berechtigung` or removed helpers like `RightsLevelManager`.
  - Remove any unused fixtures and test data that only exist for obsolete numeric rights scenarios, keeping the test suite focused on the new permission/role model.

### 10. Operational steps (migrations, cache, smoke tests)

- **10.1 Run DB migrations in the container**
  - After deploying the refactored code, but before putting traffic on the new version, run the database migrations inside the `zms-web` container:
    - `podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate --update"`
  - Verify that:
    - All new permissions/roles tables and `user_role` foreign keys are present with the expected schema.
    - No migration errors or warnings are reported.
- **10.2 Clear application caches**
  - After successful migrations, clear the application cache so new permissions/roles, Twig templates, and configuration are picked up.
  - Depending on your deployment, this may include:
    - Clearing the `cache/@` folder used by ZMS (e.g. via an application-specific cache clear command or by deleting and recreating the directory inside the container).
    - Clearing any additional opcode/template caches if applicable.
- **10.3 Post-deploy smoke checks**
  - Run a small, fixed smoke test suite on staging/production after deployment:
    - `system_admin` (superuser) can:
      - log in to `zmsadmin`,
      - open the roles/permissions admin UI,
      - create/update a role and see the effect on a test user.
    - `agent_queue` can:
      - open the Sachbearbeiter/Tresen view,
      - see the expected queues, and
      - process an appointment end-to-end.
    - `reporting_viewer` can access statistics in `zmsstatistic` but not audit logs.
    - `audit_viewer` can see logs in customer search but not statistics endpoints.


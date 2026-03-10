## Legacy rights deprecation plan

This document describes how and when the legacy numeric rights model
(`Berechtigung`, `rights.*`, `RightsLevelManager`) will be removed, and which
code paths are affected.

It is the narrative companion to sections 8 and 9 of the
`rights-permissions-refactor-updated` plan.

### 1. Current state (transition period)

- `user_role` and `role_permission` are the **authoritative** source of access.
- `Useraccount.permissions` is hydrated from roles and drives:
  - backend checks via `hasPermissions()`, `hasAnyPermission()`, `isSuperUser()`,
  - Twig via `workstation.useraccount.permissions.*`.
- Legacy artefacts that still exist during the transition:
  - `nutzer.Berechtigung` column and indexes,
  - `Useraccount.rights` object and `getRightsLevel()` logic,
  - `rights__*` computed fields and `Berechtigung`‑based filters in
    `Zmsdb/Query/Useraccount`,
  - `RightsLevelManager` helper and any feature flags that toggle “old vs new”
    behaviour,
  - templates that still reference `rights.*` (intended to be zero by the end
    of section 5).

New development MUST only use roles/permissions. Legacy structures are strictly
for compatibility until the cleanup is executed.

### 2. Preconditions before dropping legacy rights

Before starting the removal (section 8), ensure:

1. All controllers use permission names (or login‑only) and no longer depend on
   numeric `Berechtigung` ranges for access decisions.
2. All Twig templates use `permissions`/`roles` only.
3. User management flows (`zmsadmin` + `zmsapi`) read/write `user_role` and
   derive `rights` purely for backwards compatibility.
4. The atomic permissions, roles, and role bundles are frozen and documented in
   `docs/permissions-and-roles-mapping.md`.

Once these conditions are met, the system can safely stop relying on
`Berechtigung`.

### 3. Step‑by‑step cleanup (what to remove)

The following steps mirror section 8 of the plan.

#### 3.1 Database schema cleanup (8.1)

- Add a migration that:
  - drops `nutzer.Berechtigung`,
  - drops any indexes keyed on `Berechtigung` (e.g. `idx_nutzer_berechtigung`).
- Run the migration in all environments **after** the application code has been
  deployed without any `Berechtigung` usages.

This step is irreversible in production; coordinate carefully with operations.

#### 3.2 Entity and query cleanup (8.2)

- In `Zmsentities/Useraccount`:
  - remove legacy `rights.*` fields that duplicate permission semantics,
  - simplify `getDefaults()` / `getEntityMapping()` to expose only
    `permissions` (and optionally `roles`),
  - remove `getRightsLevel()` and any numeric‑level logic.
- In `Zmsdb/Query/Useraccount` and related queries:
  - remove `rights__*` computed fields based on `Berechtigung` expressions,
  - remove helper filters such as `addConditionRoleLevel()` and replace them
    with role‑ or permission‑based conditions.
- In `zmsentities/schema/useraccount.json` and dereferenced variants:
  - remove legacy rights properties so the schema only documents atomic
    permissions (and, if desired, roles).

After this step, rights‑level concepts no longer exist at the entity/query
layer.

#### 3.3 Helper and compatibility code removal (8.3)

- Delete `Helper/RightsLevelManager.php` and all usages
  (`RightsLevelManager::getLevel()`, `$possibleRights`, etc.).
- Remove any feature flags and branches that only exist to preserve the old
  behaviour (while keeping a global `RIGHTSCHECK_ENABLED` if still desired).

The goal is that no code path can silently fall back to the legacy model.

#### 3.4 UI and template cleanup (8.4)

- Confirm that all templates rely exclusively on `permissions` and/or `roles`.
- Remove any dead UI branches or explanatory texts that mention numeric rights
  levels or legacy right names.

At this point, the user‑visible model is “roles and permissions” only.

### 4. PHPUnit alignment and fixture cleanup (section 9)

To keep the test suite trustworthy:

1. **Inventory legacy tests (9.1)**:
   - find tests asserting behaviour in terms of `Berechtigung` or `rights.*`,
   - decide whether each test should be rewritten (to roles/permissions) or
     deleted (if it only covered internal mapping details).
2. **Refactor expectations (9.1, 9.2)**:
   - express all behavioural assertions in terms of:
     - roles (`agent_queue`, `appointment_admin`, `system_admin`, etc.),
     - atomic permissions (`statistic`, `logs`, `config`, …),
   - add explicit deny/403 coverage for missing permissions, especially in
     statistics, audit logs, and configuration domains,
   - add edge‑case coverage for mixed roles, superuser overrides, and
     login‑only endpoints.
3. **Fixture cleanup (9.3)**:
   - remove fixtures that only exist to exercise numeric rights levels,
   - ensure remaining fixtures declare `roles` and `permissions` explicitly.

After these steps, no test should mention `Berechtigung` or rely on the legacy
model.

### 5. Suggested timeline / rollout order

The high‑level order is:

1. Finish all refactors and tests for the new model (sections 3–7).
2. Run migrations to ensure all users have consistent `user_role` assignments.
3. Enable the new UI flows for user management and role/permission inspection.
4. Perform the schema, entity, query, and helper cleanup (section 8).
5. Align and stabilise the PHPUnit suite (section 9).

Only after step 5 should the project consider the legacy numeric rights model
fully removed. Any new feature work must be implemented solely in terms of
roles and atomic permissions.


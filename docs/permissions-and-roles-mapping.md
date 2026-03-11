## Atomic permissions and roles mapping

This document consolidates and freezes the atomic permission list and the
role-to-permission bundles as the single source of truth, based on the
existing migrations.

### 1. Atomic permissions

Defined in `zmsdb/migrations/91771408741-create-permissions-table.sql`
(`INSERT INTO permission (name, description) ...`):

- `appointment`
- `availability`
- `calldisplay`
- `cherrypick`
- `cluster`
- `config`
- `counter`
- `customersearch`
- `dayoff`
- `department`
- `emergency`
- `finishedqueue`
- `finishedqueuepast`
- `logs`
- `mailtemplates`
- `missedqueue`
- `openqueue`
- `organisation`
- `overviewcalendar`
- `parkedqueue`
- `restrictedscope`
- `scope`
- `source`
- `statistic`
- `ticketprinter`
- `useraccount`
- `waitingqueue`
- `superuser`

All backend checks and frontend visibility rules MUST use these names.

### 2. Legacy `Berechtigung` → roles mapping

Defined by `zmsdb/migrations/91771576480-migrate-users-to-new-roles.sql`
(`CASE n.Berechtigung ...`):

| Berechtigung | Role name        |
|--------------|------------------|
| 90           | `system_admin`   |
| 40           | `user_admin`     |
| 30           | `appointment_admin` |
| 5            | `audit_viewer`   |
| 0            | `agent_queue`    |

All other `Berechtigung` values are not mapped by that migration and require
explicit handling during the refactor.

During the transition period this mapping is frozen and is the only valid
mapping from numeric level to roles.

### 3. Roles → permissions bundles

Defined by `zmsdb/migrations/91771408763-create-role-permission-table.sql`
(`INSERT IGNORE INTO role_permission ...`):

- `agent_basic`
  - `appointment`, `emergency`, `counter`, `customersearch`
- `agent_queue`
  - `appointment`, `emergency`, `counter`, `customersearch`, `cherrypick`,
    `waitingqueue`, `parkedqueue`, `missedqueue`, `openqueue`, `finishedqueue`
- `agent_queue_plus`
  - Same as `agent_queue` plus: `overviewcalendar`
- `appointment_admin`
  - Same as `agent_queue_plus` plus: `restrictedscope`, `statistic`
- `reporting_viewer`
  - `statistic`
- `user_admin`
  - `useraccount`
- `audit_viewer`
  - `customersearch`, `logs`
- `system_admin`
  - `superuser` (which, at runtime, implies all other permissions)

These bundles MUST remain in sync with the `role_permission` table. Any
changes to a bundle must be implemented via a migration that updates
`role_permission`.

### 4. Special cases and non-hierarchical legacy rights

#### 4.1 `audit` vs `logs` and `statistic`

Historically, the legacy right `audit` (and some numeric level combinations)
were used to gate both statistics and log viewing. In the new model this is
split:

- Statistics (in `zmsstatistic`):
  - Guarded by the `statistic` permission.
  - Granted via roles such as `appointment_admin` and `reporting_viewer`.
  - Implemented in Warehouse* controllers as `checkRights('statistic')`.
- Audit/log viewing in customer search (in `zmsadmin`):
  - Guarded by the `logs` permission.
  - Granted via the `audit_viewer` role (which has `customersearch` + `logs`).
  - Implemented in customer-search log flows (e.g. `ProcessLog`) as
    `checkRights('logs')`.

As a result:

- `reporting_viewer` can access statistics endpoints but NOT audit logs.
- `audit_viewer` can see audit logs in customer search but NOT statistics.

#### 4.2 Superuser semantics

The legacy numeric level `Berechtigung = 90` maps to:

- the `system_admin` role, and
- the `superuser` atomic permission.

At runtime:

- `permissions.superuser = true` MUST be interpreted as “has every permission”.
- For backwards compatibility during the migration, `rights.superuser = true`
  MUST be treated equivalently until legacy `rights` is removed.

### 5. How to grant and inspect permissions (developer/admin guide)

This section explains how to work with the new model in day‑to‑day
administration and development.

#### 5.1 Granting access to a user

1. Decide which **role(s)** match the desired capability matrix, using the
   bundles above (e.g. `agent_basic`, `agent_queue`, `appointment_admin`,
   `user_admin`, `audit_viewer`, `system_admin`).
2. In the `zmsadmin` UI under `Nutzer*innen`, edit the user:
   - Use the **Rollen** checkboxes in `block/useraccount/edit.twig`
     (submitted as `roles[]`) to assign roles.
3. On save:
   - `zmsadmin/UseraccountAdd` and `UseraccountEdit` derive legacy `rights`
     from the selected roles (for backwards compatibility),
   - `zmsapi` writes the roles into `user_role`,
   - `zmsdb/Useraccount` hydrates `permissions` from `user_role → role_permission
     → permission` on read.

The **single source of truth** for effective access is therefore:

- `user_role` + `role_permission` → `permissions` on the `Useraccount` entity.

Legacy `rights` and numeric `Berechtigung` only exist as compatibility shims
until section 8 is completed.

#### 5.2 Reading permissions in backend code

- In PHP (`zmsapi`, `zmsdb`, `zmsentities`), prefer:
  - `Useraccount::hasPermissions(['appointment', 'waitingqueue'])` (all‑of),
  - `Useraccount::hasAnyPermission(['statistic', 'logs'])` (any‑of),
  - `Useraccount::isSuperUser()` for full access.
- Existing `checkRights('statistic')` / `hasRights(['statistic'])` calls are
  still supported and will consult `permissions` first, falling back to legacy
  `rights` names only when necessary.

#### 5.3 Reading permissions and roles in Twig

- Use `workstation.useraccount.permissions.*` for feature flags in templates:
  - Example: `workstation.useraccount.permissions.statistic` to guard statistics
    navigation,
  - Example: `workstation.useraccount.permissions.waitingqueue` to guard queue
    visibility.
- Use `workstation.useraccount.roles` only where a role‑level check is clearer
  than the underlying permission matrix (e.g. displaying a human‑readable
  description of assigned roles).

#### 5.4 Troubleshooting access problems

When a user cannot access a feature:

1. Check their effective roles and permissions via the **Rollen &
   Berechtigungen** admin UI in `zmsadmin` (superuser only).
2. Verify that:
   - the expected role is present in `user_role`,
   - the corresponding permissions are present in `role_permission`,
   - the controller/Twig in question is checking the correct permission name.
3. If a permission is missing:
   - either assign a different role that already carries it, or
   - extend an existing role via a migration on `role_permission`.

Never patch individual users’ `permissions` directly in the database; always
go through roles and migrations so that behaviour is reproducible across
environments.

## Atomic permissions and roles mapping

### 1. Atomic permissions (single source of truth)

The canonical list of atomic permissions is defined by the migration
`zmsdb/migrations/91771408741-create-permissions-table.sql` and MUST NOT be
changed without a corresponding migration:

- `appointment`
- `availability`
- `calldisplay`
- `cherrypick`
- `cluster`
- `config`
- `counter`
- `customersearch`
- `dayoff`
- `department`
- `emergency`
- `finishedqueue`
- `finishedqueuepast`
- `logs`
- `mailtemplates`
- `missedqueue`
- `openqueue`
- `organisation`
- `overviewcalendar`
- `parkedqueue`
- `restrictedscope`
- `scope`
- `source`
- `statistic`
- `ticketprinter`
- `useraccount`
- `waitingqueue`
- `superuser`

All backend checks and frontend visibility rules MUST use these names.

### 2. Legacy `Berechtigung` → roles mapping

The historical migration
`zmsdb/migrations/91771576480-migrate-users-to-new-roles.sql` encodes the
mapping from numeric `Berechtigung` levels to initial roles:

- `Berechtigung = 90` → `system_admin`
- `Berechtigung = 40` → `user_admin`
- `Berechtigung = 30` → `appointment_admin`
- `Berechtigung = 5` → `audit_viewer`
- `Berechtigung = 0` → `agent_queue`

All other `Berechtigung` values are ignored by that migration and are treated
as having no mapped role (i.e. they require explicit remediation during the
refactor).

This mapping is *frozen* and is the only valid source of truth when deriving
roles from numeric levels during the transition period.

### 3. Roles → permissions bundles

The canonical role bundles are defined in
`zmsdb/migrations/91771408763-create-role-permission-table.sql`:

- `agent_basic`
  - `appointment`, `emergency`, `counter`, `customersearch`
- `agent_queue`
  - `appointment`, `emergency`, `counter`, `customersearch`, `cherrypick`,
    `waitingqueue`, `parkedqueue`, `missedqueue`, `openqueue`, `finishedqueue`
- `agent_queue_plus`
  - Same as `agent_queue` plus `overviewcalendar`
- `appointment_admin`
  - Same as `agent_queue_plus` plus `restrictedscope`, `statistic`
- `reporting_viewer`
  - `statistic`
- `user_admin`
  - `useraccount`
- `audit_viewer`
  - `customersearch`, `logs`
- `system_admin`
  - `superuser` (which in turn implies all other permissions at runtime)

These bundles MUST be kept in sync with the `role_permission` table; any
changes must go through a migration.

### 4. Special legacy rights and non-hierarchical cases

#### 4.1 `audit` vs `logs` / statistics

Historically, the numeric right `audit` (level 5 and 90) was used to gate both
statistics and log viewing in some places. In the new model this is split:

- Statistics in `zmsstatistic`:
  - Guarded by the `statistic` permission.
  - Granted via roles such as `appointment_admin` and `reporting_viewer`.
  - Implemented in Warehouse* controllers as `checkRights('statistic')`.
- Audit/log viewing in customer search in `zmsadmin`:
  - Guarded by the `logs` permission.
  - Granted via the `audit_viewer` role (which has `customersearch` + `logs`).
  - Implemented in `ProcessLog` and related flows as `checkRights('logs')`.

As a result:

- `reporting_viewer` can access statistics endpoints but NOT logs.
- `audit_viewer` can access logs in customer search but NOT statistics.

This separation replaces the former overloaded `audit` meaning.

#### 4.2 `superuser` semantics

The legacy numeric `Berechtigung = 90` and `rights.superuser` flag map to the
`system_admin` role and the `superuser` atomic permission.

At runtime:

- `permissions.superuser = true` MUST be interpreted as “has every permission”.
- For backwards compatibility during the transition, `rights.superuser = true`
  MUST be treated equivalently until legacy `rights` are removed.

Any new checks that need “full access” should use the `superuser` permission
or the `system_admin` role, not numeric levels.


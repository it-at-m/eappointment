---
outline: deep
---

# Refactoring ZMS PHP Backends into Spring RefArch

## Introduction

Part of the [product-oriented RefArch roadmap](./product-oriented-refarch-roadmap.md): replace the split PHP backend packages with **two Spring Boot services** that share one MySQL schema.

| Target service      | Replaces (today)                                   |
| ------------------- | -------------------------------------------------- |
| `zmsbackend`        | `zmsdb`, `zmsapi`, server-side `zmsentities` usage |
| `zmscitizenbackend` | `zmscitizenapi`                                    |

**`zmsentities` stays in the monorepo** as the shared contract layer: JSON Schemas, validation helpers, and typed objects. **Frontend modules** such as `zmsadmin` and `zmsstatistic` keep depending on it for API response shapes and client-side validation.

---

## `zmsbackend`

Merges **`zmsdb`**, **`zmsapi`**, and the server-side use of **`zmsentities`** into one backend service.

### Today

| Package       | Role                                                                             |
| ------------- | -------------------------------------------------------------------------------- |
| `zmsentities` | Schema-backed domain objects (`Department`, `Scope`, …), validation, collections |
| `zmsdb`       | SQL queries, table mappings, caching, write paths                                |
| `zmsapi`      | HTTP controllers, permissions, JSON envelope (`Message`)                         |

### Target

Each domain gets a **vertical slice**: `api/`, `model/`, `repository/`, `service/`, `view/`, and `exception/`. Services are **split by operation** (fetch, create, update, delete) rather than one class per PHP module.

API-facing types live in `view/` and should stay **compatible** with **`zmsentities` JSON schemas** (for example `department.json`) so frontends keep working — but **`zmsbackend` does not run JSON Schema validation**. Validation is expressed in Java on `view/` types (RefArch-style validators). JPA types in `model/` map to renamed database tables (see [database refactor](../database-refactor/standardize-database-table-and-field-naming.md)).

### Why `zmsbackend`?

Benefits of merging `zmsdb`, `zmsapi`, and server-side `zmsentities` usage into one Spring Boot service on the [RefArch](https://refarch.oss.muenchen.de/) stack:

1. **Validation in one place, easier to reason about** — Today, rules are scattered across controllers, Mellon request parsing, Opis JSON Schema files, `Entity::testValid()`, and ad-hoc checks. Schemas grew into large, hard-to-maintain JSON with `oneOf` number/string unions and `$ref` chains that few people still fully understand. In `zmsbackend`, each domain validates in **`validation/`** against its **`view/`** types — one obvious place to look and change.

2. **One dependency tree instead of three Composer projects** — `zmsdb`, `zmsapi`, and shared PHP libraries mean **multiple `composer.json` files**, a large transitive dependency graph, and a steady stream of Dependabot/Renovate PRs across packages. `zmsbackend` is **one Maven `pom.xml`**, one backend artifact, one upgrade path.

3. **Built on RefArch instead of custom plumbing** — Reuse [refarch-templates](https://github.com/it-at-m/refarch-templates) for CI/CD, container builds, Keycloak integration, and gateway patterns instead of maintaining bespoke GitHub Actions that break often. Spring Boot images are typically **much faster to build** than giant PHP base images (`zmsbase`). Munich has **many Java developers**; PHP backend expertise is scarce and expensive to retain.

4. **One service, one mental model** — No more “is this in `zmsdb` or `zmsapi`?” or passing entities through three packages for a single HTTP call. Domain logic lives in a **vertical slice** (`api/` → `service/` → `repository/` → `model/`).

5. **Stronger types, fewer runtime surprises** — Java `model/` and `view/` types replace schema-backed PHP `ArrayObject` entities. Refactors and API changes are caught by the compiler and IDE, not only at runtime or in integration tests.

6. **Standard persistence and migrations** — JPA repositories and RefArch-standard database migration tooling replace hand-written query classes and scattered SQL. Aligns with the [database refactor](../database-refactor/standardize-database-table-and-field-naming.md) (clear table/column names in code).

7. **Operations and security by default** — Spring Boot Actuator, Micrometer metrics, structured logging, and RefArch security patterns (Keycloak, API gateway) match how other Munich IT products are run — not a one-off PHP stack.

8. **Faster, safer delivery** — JUnit and Spring Boot Test for unit/integration tests; ATAF and REST Assured already used in `zmsautomation`. Smaller, reproducible container images improve deploy times and reduce “works on my DDEV” drift.

9. **Easier reuse and onboarding** — Same stack as citizen-facing RefArch components (`refarch-gateway`, `zmscitizenview`). New team members and other cities can follow documented RefArch conventions instead of learning ZMS-specific PHP package boundaries.

10. **RefArch API gateway with Keycloak SSO for internal frontends** — The RefArch Spring **API gateway** ships with **Keycloak login** out of the box. Once `zmsadmin` and `zmsstatistic` move to Vue/RefArch frontends, they can authenticate through that gateway like `zmscitizenview` already does — instead of maintaining a custom **`zmsclient`** OAuth/Keycloak flow and login controllers in each PHP frontend module.

11. **No more `zmsslim` routing framework** — Today, `zmsapi` (and other PHP modules) bind HTTP routes through **`routing.php`**, Slim middleware (`Route`, `OAuthMiddleware`, …), and `BaseController` patterns from **`zmsslim`**. Spring Boot maps endpoints with **`@RestController`** / **`@RequestMapping`** (or RefArch route registries like `DepartmentRouteRegistry`) — standard Spring MVC, IDE-friendly, no custom Slim bootstrap to maintain.

12. **SLF4J/Logback instead of centralized Monolog in `zmsslim`** — PHP backends log via **`App::$log`**, wired once in **`zmsslim`**'s `Bootstrap::configureLogger()` (Monolog, JSON to stdout). Spring Boot does **not** use Monolog; it ships with **SLF4J + Logback** and RefArch logging config for structured JSON — drop the shared Monolog bootstrap and PSR-3 plumbing from the backend stack.

13. **No giant `routing.php` — routes live on controllers** — Today, `zmsapi/routing.php` is **~6,600 lines** of URL-to-controller mappings in one file, far from the handler code. In `zmsbackend`, each endpoint is declared on its **`@RestController`** (`@GetMapping`, `@PostMapping`, …) next to the method that handles it — the IDE jumps straight from route to implementation, and domain slices stay self-contained.

14. **No custom Swagger pipeline to maintain** — Today, **`zmsapi`** and **`zmscitizenapi`** each run their own doc toolchain: **`@swagger`** blocks in **`routing.php`**, **`build_swagger.js`**, **`swagger-jsdoc`**, YAML partials under **`public/doc/`**, npm scripts (`npm run doc`), and CI steps to bundle **`swagger.json`** and ship Swagger UI assets. That must be kept working **per PHP API**. **`zmsbackend`** and **`zmscitizenbackend`** use **Spring's OpenAPI support** (springdoc-openapi on the RefArch stack): annotate controllers, run locally, open **Swagger UI** at **`/swagger-ui.html`**, and try endpoints interactively — no separate npm build or hand-rolled doc generator per service.

15. **Domain controllers instead of ~166 single-action classes** — `zmsapi` implements almost every HTTP action as its **own PHP class** (`DepartmentGet`, `DepartmentList`, `ProcessFree`, …), each repeating Mellon parsing, permission checks, `Response\Message::create()`, and a hand-off to `zmsdb`. In `zmsbackend`, a **`@RestController` per domain** groups related endpoints, shares security and exception handling, and keeps request/response mapping next to the service layer — less copy-paste and far fewer files to open when changing one feature.

### Worked example: `Department` (`behoerde` → `department`)

Illustrative Spring Boot layout. Table rename: `behoerde` → `department`.

#### Folder structure

```
src/main/java/de/muenchen/zms/department/
├── api/                    # today: zmsapi controllers + routing.php
├── exception/
├── model/                  # today: zmsdb table mappings (behoerde → department)
├── repository/             # today: zmsdb Query\* + readByDepartmentId helpers
├── service/                # one service per operation (not one class per PHP module)
├── validation/             # RefArch: imperative validators on view/ types
└── view/                   # API payloads (shape compatible with zmsentities schemas)
```

#### PHP today → Java target (full `Department` slice)

| PHP (today)                                                               | Java (target)                                        |
| ------------------------------------------------------------------------- | ---------------------------------------------------- |
| `zmsapi/routing.php` (`/department/*`, …)                                 | `api/DepartmentRouteRegistry` + controllers          |
| `zmsapi\DepartmentGet`                                                    | `api/DepartmentController.getDepartment`             |
| `zmsapi\DepartmentList`                                                   | `api/DepartmentController.listDepartments`           |
| `zmsapi\DepartmentUpdate`                                                 | `api/DepartmentController.updateDepartment`          |
| `zmsapi\DepartmentDelete`                                                 | `api/DepartmentController.deleteDepartment`          |
| `zmsapi\DepartmentAddScope`                                               | `api/DepartmentController.addScope`                  |
| `zmsapi\DepartmentAddCluster`                                             | `api/DepartmentController.addCluster`                |
| `zmsapi\OrganisationByDepartment`                                         | `api/DepartmentController.getOrganisation`           |
| `zmsapi\DepartmentWorkstationList`                                        | `api/DepartmentController.listWorkstations`          |
| `zmsapi\OrganisationAddDepartment`                                        | `api/OrganisationDepartmentController.addDepartment` |
| `zmsapi\DepartmentByScopeId`                                              | `api/ScopeDepartmentController.getDepartmentByScope` |
| `zmsapi\UseraccountListByDepartments`                                     | `api/DepartmentUseraccountController`                |
| `zmsapi\UseraccountListByRoleAndDepartments`                              | `api/DepartmentUseraccountController`                |
| `zmsdb\Department` + `Query\Department`                                   | `model/`, `repository/DepartmentRepository`          |
| `zmsdb\Link`, `DayOff`, `Scope`, `Cluster`, `Workstation`, `Organisation` | matching `repository/Department*`                    |
| `zmsdb\Useraccount` (department queries)                                  | `repository/DepartmentUseraccountRepository`         |
| `zmsentities\Department` + `department.json`                              | `view/DepartmentView`                                |
| `zmsentities\Schema\Validator` + `Department::testValid()`                | `validation/ValidateDepartment`                      |
| —                                                                         | `validation/DepartmentValidationService`             |

#### Today — click through the PHP stack

Browse the **full department slice** in the monorepo: **`zmsentities`** schema and entity, all **`zmsdb`** query classes used for department CRUD and resolved references, all **`zmsapi`** controllers, and the **`routing.php`** excerpt with every department endpoint.

<DepartmentCodeExplorerToday />

#### Target — Spring Boot module in `zmsbackend`

Browse the **complete translated module**: **`api/`** controllers for every endpoint, **`repository/`** classes for each query layer, **`model/`** JPA entities, **`view/`** API types, **`service/`** (one service per operation), and **`validation/`** Java validators (no JSON Schema on the server).

<DepartmentCodeExplorerTarget />

Regenerate explorer data after PHP or target Java changes: `npm run docs:department-explorers` in `docs/`.

Frontends such as `zmsadmin` and `zmsstatistic` continue to treat API payloads as `zmsentities` types; only the producing backend service changes.

---

## `zmscitizenbackend`

Separate **citizen-facing** backend (today: PHP module **`zmscitizenapi`**).

### Today

| Package               | Role                                                                                                |
| --------------------- | --------------------------------------------------------------------------------------------------- |
| `zmscitizenapi`       | Controllers, services, citizen models (`Office`, `Service`, `ThinnedScope`, …)                      |
| `ZmsApiClientService` | HTTP client to **`zmsapi`** — fetches full `zmsentities` graphs (`Provider`, `Scope`, `Process`, …) |
| `ZmsApiFacadeService` | Orchestrates multiple **`zmsapi`** calls, merges lists, applies second-level cache                  |
| `MapperService`       | Maps giant **`zmsentities`** payloads into thinned citizen models                                   |
| `zmsentities`         | Some shared types; citizen models are mostly separate                                               |

Core data is loaded **through HTTP calls to `zmsapi`**, not from an owned query layer. Typical flows fetch **over-sized admin entities**, then **`MapperService`** projects them into **`Office`**, **`ThinnedScope`**, **`Service`**, and similar citizen DTOs — often after **several round-trips** coordinated by **`ZmsApiFacadeService`** (~900 lines today).

### Target

`zmscitizenbackend` keeps its **own citizen-facing models** (`Office`, `Service`, `ThinnedScope`, …) in `model/` and `view/`. It **does not call `zmsbackend` (previously `zmsapi`) over HTTP**. Instead it uses **its own repository layer** — the same idea as `zmsdb` in the PHP stack today: SQL (or JPA) queries owned by the citizen backend, against the **shared MySQL schema**.

Same vertical-slice layout as `zmsbackend`: `api/`, `model/`, `repository/`, `service/`, `view/`, `exception/` per domain (for example `office/`, `thinnedprocess/`, `availability/`).

Citizen models stay **thinned and opinionated** for public APIs. They are not required to match `zmsentities` schemas one-to-one; `zmsbackend` exposes fuller, schema-compatible payloads where needed.

### Why `zmscitizenbackend`?

Benefits of replacing **`zmscitizenapi`** and its **`zmsapi`** client stack with a Spring Boot service that owns its own persistence:

1. **No more mapping small citizen models from giant admin entities** — Today, **`MapperService`** (~640 lines) walks full **`zmsentities`** graphs (`Provider`, `Scope`, `Process`, `Request`, …) and manually copies fields into **`Office`**, **`ThinnedScope`**, **`ThinnedProcess`**, and related types. In **`zmscitizenbackend`**, repositories and **`view/`** types load **only what the citizen API exposes** — mapping is query design, not a maintenance-heavy translation layer.

2. **Drop the second API hop** — Every citizen read/write currently goes **`zmscitizenapi` → HTTP → `zmsapi` → `zmsdb` → MySQL**, with JSON encode/decode on both sides. **`zmscitizenbackend`** talks to the database **directly** (`service/` → `repository/` → `model/`). Fewer network hops, less serialization, lower latency on booking and availability hot paths.

3. **Tailored queries instead of “fetch everything, filter in PHP”** — **`ZmsApiFacadeService`** often loads **whole provider and scope lists** from **`zmsapi`**, merges them in memory, then caches the mapped result. Citizen backends can use **focused JPA/SQL** (joins, projections, pagination) for offices-by-service, available days, and appointment slots — **less data moved, less CPU spent shaping DTOs**.

4. **Less glue code to maintain** — **`ZmsApiClientService`**, **`ZmsApiFacadeService`**, and **`MapperService`** are tightly coupled to **`zmsapi`** routes and **`zmsentities`** shapes. A schema or endpoint change in the admin API ripples into citizen mapping and cache keys. Owned repositories **decouple** the public citizen contract from internal admin API evolution.

5. **Simpler caching story** — Second-level caches (`processed_offices`, `processed_scopes`, …) exist largely to amortize **HTTP + mapping** cost. Direct reads make caching **optional and targeted** (for example hot office lists) instead of mandatory for acceptable response times.

6. **Fewer failure modes for citizens** — Citizen booking no longer depends on **`zmsapi`** being up and fast while admin workloads (reports, bulk edits, statistics) load the same service. **`zmscitizenbackend`** scales and fails independently on its read/write paths.

7. **Same RefArch stack as `zmsbackend` and `refarch-gateway`** — One Maven project, JUnit/Spring Boot Test, Actuator metrics, and shared Munich CI/container patterns — not a separate PHP module plus HTTP client configuration (`ZMS_API_URL`, **`zmsclient`**-style plumbing).

8. **Clearer ownership of the citizen domain** — Vertical slices (`office/`, `thinnedprocess/`, `availability/`) replace a monolithic facade. Each feature owns its **API, service, repository, and view** types instead of adding branches to shared mapper/facade classes.

9. **Room to optimize hot paths deliberately** — Availability and reservation flows can get **dedicated read models and indexes** without negotiating new **`zmsapi`** endpoints or bloating admin entities that frontends never see.

10. **Easier testing** — Repository and service tests against the shared schema replace heavy mocking of **`ZmsApiClientService`** HTTP responses and mapper edge cases; ATAF/REST Assured can target one citizen Spring service end-to-end.

### Worked example: `ThinnedProcess` (citizen booking slice)

Illustrative Spring Boot layout for the citizen **`ThinnedProcess`** domain — the public API type citizens and `zmscitizenview` already use. Table today: `buerger` (future rename: `process` — see [database refactor](../database-refactor/standardize-database-table-and-field-naming.md)).

#### Naming: `ThinnedProcess`, not `Appointment`

PHP **`zmscitizenapi`** mixes names: controllers are **`Appointment*Controller`**, but every response is a **`ThinnedProcess`**. In **`zmscitizenbackend`**, the vertical slice is **`thinnedprocess/`** end-to-end — **`ThinnedProcessController`**, **`ThinnedProcessFetchService`**, **`ThinnedProcessView`** — so code matches the citizen contract.

- **HTTP paths stay `/appointment`, `/reserve-appointment`, …** — unchanged for `zmscitizenview` and existing clients.
- **All slice types use the `ThinnedProcess` prefix** — `ThinnedProcessRepository`, `ThinnedProcessValidationException`, … — so nothing is confused with admin **`zmsentities\Process`** or **`zmsdb\Process`**.
- **JPA types stay `ThinnedProcessRecord`** on `buerger` — persistence layer only; not exposed as the admin **`zmsentities\Process`** graph.

#### Folder structure

```
src/main/java/de/muenchen/zms/citizen/thinnedprocess/
├── api/                    # today: zmscitizenapi Appointment*Controller + routing.php
├── exception/
├── model/                  # JPA → buerger (ThinnedProcessRecord); not the public ThinnedProcess DTO
├── repository/             # today: ZmsApiClientService HTTP → zmsapi Process*
├── service/                # today: Appointment*Service, ZmsApiFacadeService, MapperService
├── validation/
└── view/                   # ThinnedProcessView — citizen API payload
```

#### PHP today → Java target (full `ThinnedProcess` slice)

| PHP (today)                                                              | Java (target)                                                                     |
| ------------------------------------------------------------------------ | --------------------------------------------------------------------------------- |
| `zmscitizenapi/routing.php` (`/appointment`, `/reserve-appointment`, …)  | `api/ThinnedProcessController`, `api/ThinnedProcessListController`                |
| `AppointmentByIdController`                                              | `api/ThinnedProcessController` (GET `/appointment`)                               |
| `AppointmentReserveController`                                           | `api/ThinnedProcessController` (POST `/reserve-appointment`)                      |
| `AppointmentUpdateController`                                            | `api/ThinnedProcessController` (POST `/update-appointment`)                       |
| `AppointmentConfirmController`                                           | `api/ThinnedProcessController` (POST `/confirm-appointment`)                      |
| `AppointmentPreconfirmController`                                        | `api/ThinnedProcessController` (POST `/preconfirm-appointment`)                   |
| `AppointmentCancelController`                                            | `api/ThinnedProcessController` (POST `/cancel-appointment`)                       |
| `MyAppointmentsController`                                               | `api/ThinnedProcessListController` (GET `/my-appointments`)                       |
| `AppointmentByIdService`                                                 | `service/ThinnedProcessFetchService`                                              |
| `AppointmentReserveService`                                              | `service/ThinnedProcessReserveService`                                            |
| `AppointmentUpdateService`                                               | `service/ThinnedProcessUpdateService`                                             |
| `AppointmentConfirmService`                                              | `service/ThinnedProcessConfirmService`                                            |
| `AppointmentPreconfirmService`                                           | `service/ThinnedProcessPreconfirmService`                                         |
| `AppointmentCancelService`                                               | `service/ThinnedProcessCancelService`                                             |
| `MyAppointmentsService`                                                  | `service/ThinnedProcessListService`                                               |
| `ZmsApiFacadeService::getThinnedProcessById`                             | `service/ThinnedProcessFetchService` + `service/ThinnedProcessAccessService`      |
| `ZmsApiClientService::getProcessById` (+ authenticated variant)          | `repository/ThinnedProcessRepository`, `repository/ThinnedProcessQueryRepository` |
| `ZmsApiClientService::reserveTimeslot`, `submitClientData`, status POSTs | `service/ThinnedProcessReserveService`, `ThinnedProcessWriteSupport`, …           |
| `MapperService::processToThinnedProcess`                                 | `service/ThinnedProcessAssembler`                                                 |
| `MapperService::thinnedProcessToProcess`                                 | `service/ThinnedProcessWriteSupport` (write path only)                            |
| `ValidationService` (process id, auth key, not found)                    | `validation/ThinnedProcessValidationService`, `ValidateThinnedProcessAccess`      |
| `ThinnedProcess` + `citizenapi/thinnedProcess.json`                      | `view/ThinnedProcessView` (Java validation; schema stays for frontends)           |
| `ThinnedScope`                                                           | `view/ThinnedScopeView`                                                           |
| —                                                                        | `model/ThinnedProcessRecord` (JPA → `buerger`)                                    |
| —                                                                        | `repository/ThinnedProcessProjection` (SQL join → citizen fields only)            |

#### Today — click through the PHP stack

Browse the **full appointment / `ThinnedProcess` slice** across three layers: **`zmscitizenapi`** (citizen schemas, **`ThinnedProcess`** model, **`Appointment*`** controllers/services, **`MapperService`** / facade / client), **`zmsapi`** ( **`Process*`** controllers and **`routing.php`** excerpt — the HTTP layer **`ZmsApiClientService`** calls), and **`zmsdb`** (**`Process`** query layer on **`buerger`**). Shows the full **`zmscitizenapi` → `zmsapi` → `zmsdb`** hop before mapping giant **`Process`** entities into **`ThinnedProcess`**.

<ThinnedProcessCodeExplorerToday />

#### Target — Spring Boot module in `zmscitizenbackend`

Browse the **complete translated `thinnedprocess/` module**: **`ThinnedProcessController`** (URL paths unchanged), **`repository/`** with join projection, **`ThinnedProcessView`**, per-operation **`ThinnedProcess*Service`** classes, and **`ThinnedProcessAssembler`** instead of **`MapperService`**.

<ThinnedProcessCodeExplorerTarget />

Regenerate explorer data after PHP or target Java changes: `npm run docs:thinned-process-explorers` in `docs/`.

Citizen frontends (`zmscitizenview`) keep consuming **`ThinnedProcess`**-shaped JSON; the **`citizenapi/thinnedProcess.json`** schema in **`zmsentities`** remains the contract — only the producing backend changes.

---

## Migration notes

1. **Strangle by domain** — migrate `Department*` endpoints on `zmsbackend` first; keep PHP controllers until parity tests pass.
2. **One database** — both backends read the same schema; table renames happen in migrations (see database refactor doc).
3. **Keep `zmsentities` schemas for frontends** — `zmsbackend` validates with Java on `view/` types; do not run JSON Schema in Spring.
4. **Package layout** — per domain on both backends: `api/` → `service/` → `repository/` → `model/`, API types in `view/`.

Related: [Modernize ZMS Architecture (3–5 year plan)](./product-oriented-refarch-roadmap.md) · [Standardize database table and field naming](../database-refactor/standardize-database-table-and-field-naming.md)

## ZMS Automation - ATAF Integration

This module contains **API and UI tests** for ZMS using the ATAF (Test Automation Framework) with Cucumber.

## Prerequisites

- Java 21
- Maven 3.9+
- MySQL/MariaDB database (for local testing)
- Access to Maven Central (ATAF artifacts are published under `de.muenchen.ataf:core|rest|web`).

## Project Structure

- `src/test/java/zms/api/` - Original REST-assured + JUnit tests (standalone profile)
- `src/test/java/zms/ataf/`  
  - `zms/ataf/rest/steps/` - REST step definitions (REST Assured)  
  - `zms/ataf/ui/steps/` - UI step definitions (Selenium/ATAF web)  
  - `zms/ataf/ui/pages/**` - Page objects for Admin, Statistik, Bürgeransicht, Mailinator  
- `src/test/resources/features/` - Cucumber feature files  
  - `rest/zmsapi/` - ZMS REST API features  
  - `rest/zmscitizenapi/` - Citizen REST API features  
  - `ui/zmsadmin/` - Admin UI features  
  - `ui/buergeransicht/` - Legacy eappointment citizen view UI features  
  - `ui/zmsstatistic/` - Statistik UI features  
  - `ui/zmscitizenview/` - Citizen view UI (Service Finder + full booking E2E)  
- `src/main/resources/db/migration/` - Flyway database migrations

## Running Tests

### Using the Test Script (Recommended for City Laptop)

The `zmsautomation-test` script handles database setup, migrations, and test execution:

```bash
# Run all ATAF tests (API + UI)
./zmsautomation/zmsautomation-test -Pataf-api -Pataf-ui

# Run specific tags (scenarios tagged @ignore are excluded unless you add @ignore to the expression)
./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@smoke"
# Run including ignored scenarios, e.g.:
# ./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@ignore and @web"

# Run specific API feature (path is relative to the zmsautomation module; use -Pataf-api for API-only runner)
./zmsautomation/zmsautomation-test -Pataf-api -Dcucumber.features="src/test/resources/features/rest/zmsapi/status.feature"

# Run only API tests (no Selenium)
./zmsautomation/zmsautomation-test -Pataf-api

# Run only UI tests (Selenium/ATAF web)
./zmsautomation/zmsautomation-test -Pataf-ui
```

The script will:
1. Backup the database
2. Clear caches
3. Reset database (drop tables)
4. Import base database (`.resources/zms.sql`)
5. Run Flyway migrations (Maven plugin)
6. Run PHP migrations (`zmsapi` migrate)
7. Run hourly cronjob (with retries)
8. Run minutely cronjob and slot calculation (`calculateSlots`)
9. Perform HTTP health checks (zmsapi, citizen API, CitizenView, optional refarch-gateway)
10. Set up display / browser tooling (Xvfb, driver checks)
11. Run `mvn test` with your arguments (default tag filter adds `not @ignore` unless you include `@ignore` in the expression)
12. Print test reports
13. Clear caches again
14. Restore database and Keycloak JSON backups (unless `SKIP_DB_RESTORE=1`)
15. Final cleanup (data dir, Flyway test rows, etc.; also registered on `EXIT`)

### Standalone Profile (Legacy REST-assured tests)

Runs the original JUnit-based REST-assured tests:

```bash
cd zmsautomation
mvn test -Pstandalone
```

### ATAF as default

The `ataf` Maven profile is active by default in this module, so `mvn test` / `mvn test-compile` already includes the ATAF/Cucumber/TestNG/Selenium dependencies.

### ATAF Profiles (Local Development - Manual)

Requires Artifactory access (closed-source ATAF artifacts).

- **Run all ATAF tests (API + UI)**:

```bash
cd zmsautomation
mvn test
```

- **API-only tests (REST Assured, no Selenium)**:

```bash
mvn test -Pataf-api
# optionally filter:
# mvn test -Pataf-api -Dcucumber.filter.tags="@rest"
# mvn test -Pataf-api -Dcucumber.filter.tags="@zmsapi"
# mvn test -Pataf-api -Dcucumber.filter.tags="@zmscitizenapi"
```

- **UI-only tests (Selenium/ATAF web, no REST Assured)**:

```bash
mvn test -Pataf-ui
# optionally filter:
# mvn test -Pataf-ui -Dcucumber.filter.tags="@web"
# mvn test -Pataf-ui -Dcucumber.filter.tags="@zmsadmin"
# mvn test -Pataf-ui -Dcucumber.filter.tags="@buergeransicht"
# mvn test -Pataf-ui -Dcucumber.filter.tags="@zmsstatistic"
# mvn test -Pataf-ui -Dcucumber.filter.tags="@zmscitizenview"
```

### macOS host (CLI)

From the repo root: `./cli tests install-mac-deps` (drivers + `sudo safaridriver --enable`). For Safari also enable **Safari → Develop → Allow Remote Automation**.

```bash
./cli tests run-mac-local --db-full-setup   # optional; needs MYSQL_* for your DB
./cli tests run-mac-local                   # HTTP :8090; use --api-http if needed
./cli tests trust-local-gateway             # re-import gateway cert only
```

Use `--browser safari` (or `chrome` / `firefox` / `edge` / `safari`) as needed.

## Environment Variables

Required environment variables for ATAF tests:

### API Endpoints
- `BASE_URI` - ZMS API base (default in `zmsautomation-test`: `http://web/terminvereinbarung/api/2` for Docker Compose / devcontainer). Use `http://localhost/...` when the test process runs **inside** the `web` container and should hit local Apache only.
- `CITIZEN_API_BASE_URI` - Citizen API base (default: `http://web/terminvereinbarung/api/citizen`) — **direct** to zms-web. REST steps use this; **refarch-gateway is not used** for those pings.
- `ADMIN_BASE_URI` / `STATISTIC_BASE_URI` - Defaults use `http://localhost/terminvereinbarung/.../` (typical when tests run inside the `web` container).
- `CITIZEN_VIEW_BASE_URI` / `CITIZENVIEW_PORT` - CitizenView / Vite dev server (defaults: port `8082`, base `http://citizenview:8082/`). Override if your stack uses another port (e.g. prebuilt nginx image on `8080`).
- `REFARCH_GATEWAY_OFFICES_URL` - Optional override for the extra health ping that hits the gateway (default: `http://refarch-gateway:8080/buergeransicht/api/citizen/offices-and-services/`). Same URL path the browser uses; produces lines in gateway logs.
- `SKIP_REFARCH_GATEWAY_HEALTH=1` - Skip gateway ping (e.g. no refarch-gateway container).
- **Proxies** - Health-check `curl` calls use explicit `--noproxy` so localhost and Docker service hostnames are not sent through an HTTP proxy. For Maven/browser traffic behind a corporate proxy, configure the environment as needed for your network.
- **SCREENSHOT_EVERY_STEP** - Per-step PNGs skip `about:newtab` / Firefox start page so REST Background steps do not flood artifacts. After the app URL loads, screenshots include calendar/reserve steps as usual.
- **Pass jump-in + 10489** - Valid link: `allowDisabledServicesMix` links 10489 and 10502. Pass-only still books on **10502** (see UI + REST `zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links*` JumpIn 10489 scenario).
- **10502 vs 10489** - Passkalender **10502** only exposes the three Pass services. Hauptkalender **10489** exposes non-Pass (e.g. Wohnsitzanmeldung 1063475) and Pass when combined. Jump-in non-Pass + **10502** is correctly rejected in UI.

### Database Configuration
- `MYSQL_HOST` - Database host (default: `db`)
- `MYSQL_PORT` - Database port (default: `tcp://db:3306` in `zmsautomation-test`, matching `.devcontainer/.env.template`; a plain `3306` also works)
- `MYSQL_DATABASE` - Database name (default: `db`)
- `MYSQL_USER` - Database user (default: `db`)
- `MYSQL_PASSWORD` - Database password (default: `db`)
- `MYSQL_ROOT_PASSWORD` - Root password for admin operations (default: `root`)

### UI tests (SSO)
For local UI tests (Statistik, Admin), the default SSO user is the Keycloak `ataf` user (password `vorschau`), created by Keycloak migration `.resources/keycloak/migration/07_add-system-users.yml` and related DB data in Flyway (e.g. `V16__add_keycloak_system_users.sql`). Credentials are set in `testautomation.properties` (`testautomation.userName` / `testautomation.userPassword`) or via ATAF environment variables. For other environments (e.g. ssodev.muenchen.de), override with the appropriate credentials.

### Example

```bash
export BASE_URI="http://web/terminvereinbarung/api/2"
export CITIZEN_API_BASE_URI="http://web/terminvereinbarung/api/citizen"
export MYSQL_HOST="db"
export MYSQL_PORT="tcp://db:3306"
export MYSQL_DATABASE="db"
export MYSQL_USER="db"
export MYSQL_PASSWORD="db"

cd zmsautomation && mvn test
```

## Database Setup

The ATAF tests automatically run Flyway migrations before executing tests. The migrations are located in `src/main/resources/db/migration/`.

## Test Tags

- **API tags**
  - `@rest` - All REST API tests
  - `@zmsapi` - ZMS API tests (`features/rest/zmsapi/**`)
  - `@zmscitizenapi` - Citizen API tests (`features/rest/zmscitizenapi/**`)
- **UI tags**
  - `@web` - All web UI tests
  - `@zmsadmin` - Admin UI features (`features/ui/zmsadmin/**`)
  - `@buergeransicht` - Legacy eappointment citizen view UI features (`features/ui/buergeransicht/**`)
  - `@zmsstatistic` - Statistik UI features (`features/ui/zmsstatistic/**`)
  - `@zmscitizenview` - Citizen view webcomponent UI (`features/ui/zmscitizenview/**`)
  - `@jumpin` - Booking scenarios that open jump-in URL (combination step first)
  - `@ruppertstrasse` - Ruppertstraße Passkalender (10502) style flows
  - `@passkalender` - Passkalender 10502 (three Pass services only); invalid jump-in if non-Pass + 10502
  - `@hauptkalender` - Hauptkalender (10489); non-Pass + Pass combinable
  - `@abholung` - Abholung 10295182 / standort 10492 only (KVR-II/211)
  - `@executeLocally` - Forces ATAF web tests to use local browser drivers instead of Selenium Grid; add this tag on UI scenarios/features that must not use `localhost:4444` in CI
  - `@allowDisabledServicesMix` - Jump-in scenario that may mix disabled services (environment-dependent)
- **Other**
  - `@smoke` - Smoke tests (critical path)

`@executeLocally` is a UI-only tag (`@web` scenarios). Do not add it to pure REST scenarios (`@rest`), because they do not initialize Selenium/WebDriver.

## Feature Files

### API Features (`src/test/resources/features/rest/`)

#### ZMS API (`rest/zmsapi/`)
- `status.feature` - Status endpoint tests (converted from `StatusEndpointTest`)

#### Citizen API (`rest/zmscitizenapi/`)
- `zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links_citizenapi.feature` - Ruppertstraße Citizen API booking (10502 / 10489 / 10492, jump-in)

Additional REST features (availability, offices-and-services, etc.) may be added over time; this list reflects files currently present under `features/rest/`.

### UI Features (`src/test/resources/features/ui/`)

#### Admin UI (`ui/zmsadmin/`)
- Cucumber features for the Admin web UI (Terminadministration, Behörden & Standorte, Workview, etc.)

#### Legacy Bürgeransicht UI (`ui/buergeransicht/`)
- Features that automate the legacy eappointment Bürgeransicht frontend.

#### zmscitizenview UI (`ui/zmscitizenview/`)
- `zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links.feature` - zmscitizenview Ruppertstraße UI booking (Kalenderansicht); Ort = checkbox list or single-provider teaser; slot wait until **MucSpinner** (`.m-spinner-container`) cleared after day load + timeslot in DOM; `#provider-*` on reserve, preconfirm, confirm

#### Statistik UI (`ui/zmsstatistic/`)
- Features for the Statistik web UI (Dienstleistungsstatistik, Kundenstatistik, CSV export, etc.)

## CI/CD

GitHub Actions: `.github/workflows/zmsautomation-workflow.yaml` checks out the repo, copies `.devcontainer/.env.template` → `.env`, pulls **prebuilt PHP module images** from GHCR (`zmsadmin`, `zmsapi`, …), starts a subset of `.devcontainer/docker-compose.yaml` (`web`, `db`, `citizenview`, `refarch-gateway`, `keycloak`, `init-keycloak`; no phpMyAdmin), installs Java/Maven/browsers into `zms-web`, injects module trees from those images, then runs `zmsautomation/zmsautomation-test` inside `zms-web` via `docker exec`. **CitizenView** is the same Node + Vite dev service as in the devcontainer (`npm install` + dev server on port **8082**), not a separate prebuilt CitizenView image.

## Migration Notes

- `zmsautomation` uses ATAF + Cucumber; CI/workflows may pin environments separately.

## Phase 6: Migration Examples

Older planning docs described extra template features (data-driven outlines, error-handling, cancellation, etc.). **This branch currently ships the REST files listed above** plus the UI suites under `features/ui/`; use those as living examples when porting JUnit tests to Cucumber.

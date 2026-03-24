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
  - `api/zmsapi/` - ZMS REST API features  
  - `api/zmscitizenapi/` - Citizen REST API features  
  - `ui/zmsadmin/` - Admin UI features  
  - `ui/buergeransicht/` - Legacy eappointment citizen view UI features  
  - `ui/zmsstatistic/` - Statistik UI features  
  - `ui/zmscitizenview/` - Citizen view UI (Service Finder + full booking E2E)  
- `src/main/resources/db/migration/` - Flyway database migrations

## Running Tests

### Using the Test Script (Recommended for City Laptop)

The `zmsautomation-test` script handles database setup, migrations, and test execution:

```bash
# Set required environment variables
export MYSQL_HOST="db"
export MYSQL_PORT="3306"
export MYSQL_DATABASE="zmsbo"
export MYSQL_USER="zmsbo"
export MYSQL_PASSWORD="zmsbo"
export CACHE_DIR="/path/to/cache"
export BASE_URI="http://localhost/terminvereinbarung/api/2"
export CITIZEN_API_BASE_URI="http://localhost/terminvereinbarung/api/citizen"

# Run all ATAF tests (API + UI)
./zmsautomation/zmsautomation-test

# Run specific tags (scenarios tagged @ignore are excluded unless you add @ignore to the expression)
./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@smoke"
# Run including ignored scenarios, e.g.:
# ./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@ignore and @web"

# Run specific API feature
./zmsautomation/zmsautomation-test -Dcucumber.features="src/test/resources/features/api/zmsapi/status.feature"

# Run only API tests (no Selenium)
./zmsautomation/zmsautomation-test -Pataf-api

# Run only UI tests (Selenium/ATAF web)
./zmsautomation/zmsautomation-test -Pataf-ui
```

The script will:
1. Backup the database
2. Clear caches
3. Reset database (drop tables)
4. Import base database
5. Run Flyway migrations
6. Run PHP migrations
7. Run hourly cronjob
8. Perform health checks
9. Run ATAF tests with `-Pataf` profile
10. Display test results
11. Restore database
12. Cleanup

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
# mvn test -Pataf-api -Dcucumber.filter.tags="@zmscitizenapi "
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
./cli tests run-mac-local                   # HTTPS :8091 + gateway truststore on first run; use --api-http / --skip-gateway-trust if needed
./cli tests trust-local-gateway             # re-import gateway cert only
```

Use `--browser safari` (or `chrome` / `firefox` / `edge` / `safari`) as needed.

### GitHub Actions (Safari)

The `zmsautomation-workflow.yaml` can run Safari when you select `browser: safari` via `workflow_dispatch`.

- The job runs on a macOS runner and drives Safari via WebDriver.
- The workflow maps Apache HTTPS like local dev (`8091:443`), so health checks and `BASE_URI` use **`https://localhost:8091/...`** (same idea as `./cli tests run-mac-local`).
- The workflow runs `sudo safaridriver --enable` and then executes `zmsautomation-test` on the runner host (while the app stack stays in Docker containers).
- If Safari still blocks WebDriver on the runner, switch to a self-hosted Mac runner with “Allow Remote Automation” enabled (host setup is required).

To validate the setup:
- Trigger the workflow with `browser: chrome` (Ubuntu path).
- Trigger the workflow with `browser: safari` (macOS path).
- Check scheduled runs still use the Ubuntu matrix (Chrome/Firefox only).

## Environment Variables

Required environment variables for ATAF tests:

### API Endpoints
- `BASE_URI` - Base URI for ZMS API (default: `http://localhost/terminvereinbarung/api/2`)
- `CITIZEN_API_BASE_URI` - Base URI for Citizen API (default: `http://localhost/terminvereinbarung/api/citizen`) — **direct** to zms-web (`/terminvereinbarung/api/citizen/...`). REST steps use this; **refarch-gateway is not used** for those pings.
- `REFARCH_GATEWAY_OFFICES_URL` - Optional override for the extra health ping that hits the gateway (default: `http://refarch-gateway:8080/buergeransicht/api/citizen/offices-and-services/`). Same URL path the browser uses; produces lines in gateway logs.
- `SKIP_REFARCH_GATEWAY_HEALTH=1` - Skip gateway ping (e.g. no refarch-gateway container).
- **zmscitizenview ping (502)** - If `zmsautomation-test` reports 502 for `http://citizenview:8082` while `curl` from a container works, `HTTP_PROXY` was routing that URL to the corporate gateway. The script pings zmscitizenview with `--noproxy '*'` so the request stays on Docker DNS.
- **SCREENSHOT_EVERY_STEP** - Per-step PNGs skip `about:newtab` / Firefox start page so REST Background steps do not flood artifacts. After the app URL loads, screenshots include calendar/reserve steps as usual.
- **Pass jump-in + 10489** - Valid link: `allowDisabledServicesMix` links 10489 and 10502. Pass-only still books on **10502** (see UI + REST `zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links*` JumpIn 10489 scenario).
- **10502 vs 10489** - Passkalender **10502** only exposes the three Pass services. Hauptkalender **10489** exposes non-Pass (e.g. Wohnsitzanmeldung 1063475) and Pass when combined. Jump-in non-Pass + **10502** is correctly rejected in UI.

### Database Configuration
- `MYSQL_HOST` - Database host (default: `db`)
- `MYSQL_PORT` - Database port (default: `3306`)
- `MYSQL_DATABASE` - Database name (default: `zmsbo`)
- `MYSQL_USER` - Database user (default: `zmsbo`)
- `MYSQL_PASSWORD` - Database password (default: `zmsbo`)

### UI tests (SSO)
For local UI tests (Statistik, Admin), the default SSO user is the Keycloak `ataf` user (password `vorschau`), created by Keycloak migration `07_add-ataf-user.yml` and ZMS Flyway migration `V10__add_ataf_keycloak_user.sql`. Credentials are set in `testautomation.properties` (`testautomation.userName` / `testautomation.userPassword`) or via ATAF environment variables. For other environments (e.g. ssodev.muenchen.de), override with the appropriate credentials.

### Example

```bash
export BASE_URI="http://localhost:8080/terminvereinbarung/api/2"
export CITIZEN_API_BASE_URI="http://localhost:8080/terminvereinbarung/api/citizen"
export MYSQL_HOST="db"
export MYSQL_PORT="3306"
export MYSQL_DATABASE="zmsbo"
export MYSQL_USER="zmsbo"
export MYSQL_PASSWORD="zmsbo"

mvn test
```

## Database Setup

The ATAF tests automatically run Flyway migrations before executing tests. The migrations are located in `src/main/resources/db/migration/`.

## Test Tags

- **API tags**
  - `@rest` - All REST API tests
  - `@zmsapi` - ZMS API tests (`features/api/zmsapi/**`)
  - `@zmscitizenapi ` - Citizen API tests (`features/api/zmscitizenapi/**`)
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

### API Features (`src/test/resources/features/api/`)

#### ZMS API (`api/zmsapi/`)
- `status.feature` - Status endpoint tests (converted from StatusEndpointTest)
- `availability.feature` - Appointment availability tests
- `appointments.feature` - Appointment management tests
- `scopes.feature` - Scope information tests (Phase 6 example)
- `error-handling.feature` - Error handling scenarios (Phase 6 example)
- `data-driven-example.feature` - Data-driven testing examples (Phase 6 example)

#### Citizen API (`api/zmscitizenapi/`)
- `offices-and-services.feature` - Offices and services endpoint (converted from OfficesAndServicesEndpointTest)
- `zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links_citizenapi.feature` - Ruppertstraße Citizen API booking (10502 / 10489 / 10492, jump-in)
- `cancellation.feature` - Appointment cancellation flow (Phase 6 example)

### UI Features (`src/test/resources/features/ui/`)

#### Admin UI (`ui/zmsadmin/`)
- Cucumber features for the Admin web UI (Terminadministration, Behörden & Standorte, Workview, etc.)

#### Legacy Bürgeransicht UI (`ui/buergeransicht/`)
- Features that automate the legacy eappointment Bürgeransicht frontend.

#### zmscitizenview UI (`ui/zmscitizenview/`)
- `ServiceFinder.feature` - Start page / Service Finder visible (English)
- `zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links.feature` - zmscitizenview Ruppertstraße UI booking (Kalenderansicht); Ort = checkbox list or single-provider teaser; slot wait until **MucSpinner** (`.m-spinner-container`) cleared after day load + timeslot in DOM; `#provider-*` on reserve, preconfirm, confirm

#### Statistik UI (`ui/zmsstatistic/`)
- Features for the Statistik web UI (Dienstleistungsstatistik, Kundenstatistik, CSV export, etc.)

### Migration Guide
- `MIGRATION_GUIDE.md` - Guide for converting JUnit tests to Cucumber features (Phase 6)

## CI/CD

See `.github/workflows/` for automation that runs `zmsautomation` with the expected stack and env vars.

## Migration Notes

- `zmsautomation` uses ATAF + Cucumber; CI/workflows may pin environments separately.

## Phase 6: Migration Examples

Phase 6 includes example feature files demonstrating:
- **Data-driven testing** with Scenario Outlines (`data-driven-example.feature`)
- **Error handling** scenarios (`error-handling.feature`)
- **Additional API endpoints** (`scopes.feature`, `cancellation.feature`)
- **Migration guide** (`MIGRATION_GUIDE.md`) with conversion patterns and best practices

These examples serve as templates for converting additional JUnit tests to Cucumber features. See `src/test/resources/features/MIGRATION_GUIDE.md` for detailed conversion examples and patterns.

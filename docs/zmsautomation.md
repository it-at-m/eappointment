# zmsautomation Documentation

## ZMS Automation - ATAF Integration

This module contains API and UI tests for ZMS using ATAF (Test Automation Framework) with Cucumber.
`zmsautomation` is built on [it-at-m/agile-test-automation-framework](https://github.com/it-at-m/agile-test-automation-framework).

## Prerequisites

- Java 21
- Maven 3.9+
- MySQL/MariaDB database (for local testing)
- Access to Maven Central (ATAF artifacts are published under `de.muenchen.ataf:core|rest|web`)

## Project Structure

- `src/test/java/zms/api/` - original REST-assured + JUnit tests (standalone profile)
- `src/test/java/zms/ataf/`
  - `zms/ataf/rest/steps/` - REST step definitions (REST Assured)
  - `zms/ataf/ui/steps/` - UI step definitions (Selenium/ATAF web)
  - `zms/ataf/ui/pages/**` - page objects for Admin, Statistik, Buergeransicht, Mailinator
- `src/test/resources/features/` - Cucumber feature files
  - `rest/zmsapi/` - ZMS REST API features
  - `rest/zmscitizenapi/` - Citizen REST API features
  - `ui/zmsadmin/` - Admin UI features
  - `ui/buergeransicht/` - legacy eappointment citizen view UI features
  - `ui/zmsstatistic/` - Statistik UI features
  - `ui/zmscitizenview/` - CitizenView UI (Service Finder + full booking E2E)
- `src/main/resources/db/migration/` - Flyway database migrations

## Running Tests

### Using the Test Script (recommended)

The `zmsautomation-test` script handles database setup, migrations, and test execution.

```bash
# Run all ATAF tests (API + UI)
./zmsautomation/zmsautomation-test -Pataf-api -Pataf-ui

# Run specific tags (scenarios tagged @ignore are excluded unless you include @ignore in the expression)
./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@smoke"
# Run including ignored scenarios, for example:
# ./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@ignore and @web"

# Run specific API feature (path is relative to the zmsautomation module)
./zmsautomation/zmsautomation-test -Pataf-api -Dcucumber.features="src/test/resources/features/rest/zmsapi/status.feature"

# Run only API tests (no Selenium)
./zmsautomation/zmsautomation-test -Pataf-api

# Run only UI tests (Selenium/ATAF web)
./zmsautomation/zmsautomation-test -Pataf-ui
```

The script will:

1. Back up the database
2. Clear caches
3. Reset database (drop tables)
4. Import base database (`.resources/zms.sql`)
5. Run Flyway migrations (Maven plugin)
6. Run PHP migrations (`zmsapi` migrate)
7. Run hourly cronjob (with retries)
8. Run minutely cronjob and slot calculation (`calculateSlots`)
9. Perform HTTP health checks (zmsapi, citizen API, CitizenView, optional refarch-gateway)
10. Set up display/browser tooling (Xvfb, driver checks)
11. Run `mvn test` with your arguments (default tag filter adds `not @ignore` unless you include `@ignore`)
12. Print test reports
13. Clear caches again
14. Restore database and Keycloak JSON backups (unless `SKIP_DB_RESTORE=1`)
15. Final cleanup (data dir, Flyway test rows, etc.; also registered on `EXIT`)

### Standalone Profile (legacy REST-assured tests)

Runs the original JUnit-based REST-assured tests:

```bash
cd zmsautomation
mvn test -Pstandalone
```

### ATAF as default

The `ataf` Maven profile is active by default in this module, so `mvn test` and `mvn test-compile` already include ATAF/Cucumber/TestNG/Selenium dependencies.

### ATAF Profiles (local development)

- Run all ATAF tests (API + UI):

```bash
cd zmsautomation
mvn test
```

- API-only tests (REST Assured, no Selenium):

```bash
mvn test -Pataf-api
# optionally filter:
# mvn test -Pataf-api -Dcucumber.filter.tags="@rest"
# mvn test -Pataf-api -Dcucumber.filter.tags="@zmsapi"
# mvn test -Pataf-api -Dcucumber.filter.tags="@zmscitizenapi"
```

- UI-only tests (Selenium/ATAF web, no REST Assured):

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

From the repo root:

```bash
./cli tests install-mac-deps
```

For Safari also enable: Safari -> Develop -> Allow Remote Automation.

```bash
./cli tests run-mac-local --db-full-setup
./cli tests run-mac-local
./cli tests trust-local-gateway
```

Use `--browser safari` (or `chrome`, `firefox`, `edge`, `safari`) as needed.

## Environment Variables

### API Endpoints

- `BASE_URI` - ZMS API base
- `CITIZEN_API_BASE_URI` - Citizen API base (REST steps call zms-web directly)
- `ADMIN_BASE_URI` / `STATISTIC_BASE_URI` - admin/statistic bases
- `CITIZEN_VIEW_BASE_URI` / `CITIZENVIEW_PORT` - CitizenView/Vite server (default `8082`)
- `REFARCH_GATEWAY_OFFICES_URL` - optional gateway health ping URL
- `SKIP_REFARCH_GATEWAY_HEALTH=1` - skip gateway ping

### Database Configuration

- `MYSQL_HOST` (default `db`)
- `MYSQL_PORT` (default `tcp://db:3306`)
- `MYSQL_DATABASE` (default `db`)
- `MYSQL_USER` (default `db`)
- `MYSQL_PASSWORD` (default `db`)
- `MYSQL_ROOT_PASSWORD` (default `root`)

### UI tests (SSO)

For local UI tests (Statistik, Admin), the default SSO user is Keycloak `ataf` (password `vorschau`) from Keycloak migration data.

## Example

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

ATAF tests automatically run Flyway migrations before executing tests. Migrations are under `src/main/resources/db/migration/`.

## Some Test Tag Examples

- API tags:
  - `@rest`
  - `@zmsapi`
  - `@zmscitizenapi`
- UI tags:
  - `@web`
  - `@zmsadmin`
  - `@buergeransicht`
  - `@zmsstatistic`
  - `@zmscitizenview`
  - `@jumpin`
  - `@ruppertstrasse`
  - `@passkalender`
  - `@hauptkalender`
  - `@abholung`
  - `@executeLocally`
  - `@allowDisabledServicesMix`
- Other:
  - `@smoke`

`@executeLocally` is a UI-only tag (`@web` scenarios), not for pure REST scenarios.

## Feature Files

### API Features (`src/test/resources/features/rest/`)

- `rest/zmsapi/status.feature` - status endpoint tests
- `rest/zmscitizenapi/zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links_citizenapi.feature` - Citizen API booking flow

### UI Features (`src/test/resources/features/ui/`)

- `ui/zmsadmin/` - Admin UI features
- `ui/buergeransicht/` - legacy Buergeransicht features
- `ui/zmscitizenview/` - CitizenView booking UI features
- `ui/zmsstatistic/` - Statistik UI features

## zmsautomation in GitHub Workflows

GitHub Actions workflow: `.github/workflows/zmsautomation-workflow.yaml`.

Documentation to come.

## zmsautomation in Safari on macOS outside the container

You can already run Safari-based automation outside the container on macOS.
This is currently required for Safari because there is no Safari WebDriver runtime for Linux ARM/AMD containers.

We already provide a CLI setup/run flow in `cli_test.py`:

```bash
# install local macOS test dependencies (includes: sudo safaridriver --enable)
./cli tests install-mac-deps

# run local tests on macOS with Safari
./cli tests run-mac-local --browser safari
```

In Safari, you must also enable:

- `Safari -> Develop -> Allow Remote Automation`

## Migration Notes

- `zmsautomation` uses ATAF + Cucumber; CI/workflows may pin environments separately.


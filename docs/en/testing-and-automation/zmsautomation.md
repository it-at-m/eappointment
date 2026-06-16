# zmsautomation Documentation

## ZMS Automation - [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) Integration

This module contains API and UI tests for ZMS using [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) (Test Automation Framework) with Cucumber.
`zmsautomation` is built on [it-at-m/agile-test-automation-framework](https://it-at-m.github.io/agile-test-automation-framework/). It uses ATAF **without Jira** (features in Git) and **local Keycloak** for SSO — see [Local Keycloak setup](#local-keycloak-setup) and the ATAF guide [Standalone Usage (No Jira, Local Keycloak)](https://it-at-m.github.io/agile-test-automation-framework/usage/standalone-without-jira.html).

## Prerequisites

- Java 21
- Maven 3.9+
- MySQL/MariaDB database (for local testing)
- Access to Maven Central ([ATAF](https://it-at-m.github.io/agile-test-automation-framework/) artifacts are published under `de.muenchen.ataf:core|rest|web`)

## Project Structure

- `src/test/java/zms/api/` - original REST-assured + JUnit tests (standalone profile)
- `src/test/java/zms/ataf/`
  - `zms/ataf/rest/steps/` - REST step definitions (REST Assured)
  - `zms/ataf/ui/steps/` - UI step definitions (Selenium/[ATAF](https://it-at-m.github.io/agile-test-automation-framework/) web)
  - `zms/ataf/ui/pages/**` - page objects for Admin, Statistik, Buergeransicht, Mailinator
- `src/test/resources/features/` - Cucumber feature files
  - `rest/zmsapi/` - ZMS REST API features
  - `rest/zmscitizenapi/` - Citizen REST API features
  - `ui/zmsadmin/` - Admin UI features
  - `ui/buergeransicht/` - deprecated legacy citizen frontend UI features from `it-at-m/eappointment-buergeransicht` (not used for `zmscitizenview`)
  - `ui/zmsstatistic/` - Statistik UI features
  - `ui/zmscitizenview/` - CitizenView UI (Service Finder + full booking E2E)
- `src/main/resources/db/migration/` - Flyway database migrations

## Running Tests

### Using the Test Script (recommended)

The `zmsautomation-test` script handles database setup, migrations, and test execution.

```bash
# Run all [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) tests (API + UI)
./zmsautomation/zmsautomation-test -Pataf-api -Pataf-ui

# Run specific tags (scenarios tagged @ignore are excluded unless you include @ignore in the expression)
./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@smoke"
# Run including ignored scenarios, for example:
# ./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@ignore and @web"

# Run specific API feature (path is relative to the zmsautomation module)
./zmsautomation/zmsautomation-test -Pataf-api -Dcucumber.features="src/test/resources/features/rest/zmsapi/status.feature"

# Run only API tests (no Selenium)
./zmsautomation/zmsautomation-test -Pataf-api

# Run only UI tests (Selenium/[ATAF](https://it-at-m.github.io/agile-test-automation-framework/) web)
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

### [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) as default

The `ataf` Maven profile is active by default in this module, so `mvn test` and `mvn test-compile` already include [ATAF](https://it-at-m.github.io/agile-test-automation-framework/)/Cucumber/TestNG/Selenium dependencies.

### [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) Profiles (local development)

- Run all [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) tests (API + UI):

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

- UI-only tests (Selenium/[ATAF](https://it-at-m.github.io/agile-test-automation-framework/) web, no REST Assured):

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

## Local Keycloak setup

`zmsautomation` uses [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) **without Jira**: all Cucumber features live under `src/test/resources/features/`, and UI tests authenticate against a **local Keycloak** — the same [keycloakmigration](https://github.com/klg71/keycloakmigration) pattern as the [RefArch stack](https://github.com/it-at-m/refarch-templates/tree/main/stack/keycloak/migration). You do not need corporate or government SSO (`ssodev`) for local runs.

See also the ATAF handbook: [Standalone Usage (No Jira, Local Keycloak)](https://it-at-m.github.io/agile-test-automation-framework/usage/standalone-without-jira.html).

### Docker Compose services

Keycloak and the migration sidecar are defined in:

- [`.ddev/docker-compose.keycloak.yaml`](https://github.com/it-at-m/eappointment/blob/main/.ddev/docker-compose.keycloak.yaml) (DDEV)
- [`.devcontainer/docker-compose.yaml`](https://github.com/it-at-m/eappointment/blob/main/.devcontainer/docker-compose.yaml) (devcontainer / Podman)

Both stacks run:

| Service         | Role                                                                                        |
| --------------- | ------------------------------------------------------------------------------------------- |
| `keycloak`      | `quay.io/keycloak/keycloak:26.6.3`, `start-dev`, `KC_HTTP_RELATIVE_PATH=/auth`, port `8080` |
| `init-keycloak` | `klg71/keycloakmigration:0.2.129`, applies migrations once Keycloak is up                   |

`init-keycloak` mounts [`.resources/keycloak/migration/`](https://github.com/it-at-m/eappointment/tree/main/.resources/keycloak/migration) and reads `KEYCLOAK_CHANGELOG=/migration/keycloak-changelog.yml`.

### Migration changelog

The changelog applies realm configuration in order:

```yaml
includes:
  - path: 01_init-realm.yml # realm zms
  - path: 02_add-clients.yml # OIDC client zms, redirect URIs for admin/statistic
  - path: 03_add-roles.yml
  - path: 04_add-users.yml
  - path: 05_assign-roles.yml
  - path: 06_zms-audience.yml
  - path: 07_add-system-users.yml # test user ataf (password vorschau)
  - path: 08_add-role-test-users.yml
```

This mirrors the RefArch approach ([`stack/docker-compose.yml`](https://github.com/it-at-m/refarch-templates/blob/4735e9f425a29e9cd38eafc6cd34b5da705f0574/stack/docker-compose.yml#L52)) but uses a ZMS-specific realm, clients, and users.

### Hostname `keycloak`

Applications and browser redirects expect the hostname `keycloak`, not `localhost`. Add `127.0.0.1 keycloak` to your hosts file and restart the stack — see [Local Keycloak Setup](../setup-and-development/local-keycloak-setup.md).

### ATAF test properties

`zmsautomation/src/test/resources/testautomation.properties` maps ATAF to the migrated user and bypasses the corporate proxy for Docker hostnames:

```properties
testautomation.userName=ataf
testautomation.userPassword=vorschau
testautomation.noProxy=keycloak,citizenview,refarch-gateway,localhost,127.0.0.1
```

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

[ATAF](https://it-at-m.github.io/agile-test-automation-framework/) tests automatically run Flyway migrations before executing tests. Migrations are under `src/main/resources/db/migration/`.

## Some Test Tag Examples

- API tags:
  - `@rest`
  - `@zmsapi`
  - `@zmscitizenapi`
- UI tags:
  - `@web`
  - `@zmsadmin`
  - `@buergeransicht` (deprecated legacy frontend; not used for `zmscitizenview`)
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
- `ui/buergeransicht/` - deprecated legacy Buergeransicht features from `it-at-m/eappointment-buergeransicht` (not used for `zmscitizenview`)
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

- `zmsautomation` uses [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) + Cucumber; CI/workflows may pin environments separately.

## Known limitations

### Booking tests on public holidays

By design, slot calculation does not create appointments on public holidays (dates in the `feiertage` table seeded by migration V11). The test data for opening hours in zmsautomation (seeded relative to the current date in migrations such as V10 and V19) can overlap with a holiday. When that happens, there may be zero bookable slots on the “first available day,” and booking-related scenarios (Citizen API and CitizenView flows) can fail.

This is expected behavior. Action: re-run the pipeline on the next non‑holiday working day (or run locally on a non‑holiday date).

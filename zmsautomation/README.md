# ZMS Automation - ATAF Integration

This module contains API tests for ZMS using the ATAF (Test Automation Framework) with Cucumber.

## Prerequisites

- Java 21
- Maven 3.9+
- MySQL/MariaDB database (for local testing)
- Access to Artifactory (for ATAF dependencies - city laptop only)

## Project Structure

- `src/test/java/zms/api/` - Original REST-assured + JUnit tests (standalone profile)
- `src/test/java/zms/ataf/` - ATAF-based Cucumber tests
- `src/test/resources/features/` - Cucumber feature files
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

# Run all ATAF tests
./zmsautomation/zmsautomation-test

# Run specific tags
./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@smoke"

# Run specific feature
./zmsautomation/zmsautomation-test -Dcucumber.features="src/test/resources/features/zmsapi/status.feature"
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

### Standalone Profile (Default - CI)

Runs the original JUnit-based REST-assured tests:

```bash
cd zmsautomation
mvn test
```

This profile is used in GitHub Actions CI.

### ATAF Profile (Local Development - Manual)

Runs Cucumber/ATAF tests manually. Requires Artifactory access:

```bash
cd zmsautomation
mvn test -Pataf
```

Run specific tags:

```bash
mvn test -Pataf -Dcucumber.filter.tags="@smoke"
```

Run specific feature:

```bash
mvn test -Pataf -Dcucumber.features="src/test/resources/features/zmsapi/status.feature"
```

## Environment Variables

Required environment variables for ATAF tests:

### API Endpoints
- `BASE_URI` - Base URI for ZMS API (default: `http://localhost:8080/terminvereinbarung/api/2`)
- `CITIZEN_API_BASE_URI` - Base URI for Citizen API (default: `http://localhost:8080/terminvereinbarung/api/citizen`)

### Database Configuration
- `MYSQL_HOST` - Database host (default: `db`)
- `MYSQL_PORT` - Database port (default: `3306`)
- `MYSQL_DATABASE` - Database name (default: `zmsbo`)
- `MYSQL_USER` - Database user (default: `zmsbo`)
- `MYSQL_PASSWORD` - Database password (default: `zmsbo`)

### Example

```bash
export BASE_URI="http://localhost:8080/terminvereinbarung/api/2"
export CITIZEN_API_BASE_URI="http://localhost:8080/terminvereinbarung/api/citizen"
export MYSQL_HOST="db"
export MYSQL_PORT="3306"
export MYSQL_DATABASE="zmsbo"
export MYSQL_USER="zmsbo"
export MYSQL_PASSWORD="zmsbo"

mvn test -Pataf
```

## Database Setup

The ATAF tests automatically run Flyway migrations before executing tests. The migrations are located in `src/main/resources/db/migration/`.

## Test Tags

- `@rest` - All REST API tests
- `@zmsapi` - ZMS API tests
- `@citizenapi` - Citizen API tests
- `@smoke` - Smoke tests (critical path)

## Feature Files

### ZMS API Features
- `status.feature` - Status endpoint tests (converted from StatusEndpointTest)
- `availability.feature` - Appointment availability tests
- `appointments.feature` - Appointment management tests
- `scopes.feature` - Scope information tests (Phase 6 example)
- `error-handling.feature` - Error handling scenarios (Phase 6 example)
- `data-driven-example.feature` - Data-driven testing examples (Phase 6 example)

### Citizen API Features
- `offices-and-services.feature` - Offices and services endpoint (converted from OfficesAndServicesEndpointTest)
- `booking.feature` - Appointment booking flow
- `cancellation.feature` - Appointment cancellation flow (Phase 6 example)

### Migration Guide
- `MIGRATION_GUIDE.md` - Guide for converting JUnit tests to Cucumber features (Phase 6)

## CI/CD

- **GitHub Actions**: Uses `zmsautomation` module (currently disabled until ATAF is open source)
- **Local Development**: Use `zmsautomation` module with ATAF profile

## Migration Notes

- `zmsautomation` is the new module with ATAF integration and Cucumber
- GitHub Actions workflow is set up but disabled until ATAF becomes open source
- ATAF tests require Artifactory access (city laptop only) until Phase 7
- The old `zmsapiautomation` module remains for reference but is no longer actively maintained

## Phase 6: Migration Examples

Phase 6 includes example feature files demonstrating:
- **Data-driven testing** with Scenario Outlines (`data-driven-example.feature`)
- **Error handling** scenarios (`error-handling.feature`)
- **Additional API endpoints** (`scopes.feature`, `cancellation.feature`)
- **Migration guide** (`MIGRATION_GUIDE.md`) with conversion patterns and best practices

These examples serve as templates for converting additional JUnit tests to Cucumber features. See `src/test/resources/features/MIGRATION_GUIDE.md` for detailed conversion examples and patterns.

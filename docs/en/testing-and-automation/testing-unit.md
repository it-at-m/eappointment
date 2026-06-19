# Unit Testing

To run unit tests locally refer to the [Github Workflows](https://github.com/it-at-m/eappointment/blob/main/.github/workflows/unit-tests.yaml) and in your local docker container run:

## Unit Testing the PHP Modules

### Using DDEV

```bash
ddev ssh
```

```bash
cd {zmsadmin, zmscalldisplay, zmsdldb, zmsentities, zmsmessaging, zmsslim, zmsstatistic, zmsticketprinter}
```

```bash
./vendor/bin/phpunit
```

### Using Podman

```bash
podman exec -it zms-web bash
```

```bash
cd {zmsadmin, zmscalldisplay, zmsdldb, zmsentities, zmsmessaging, zmsslim, zmsstatistic, zmsticketprinter}
```

```bash
./vendor/bin/phpunit
```

Useful flags for `./vendor/bin/phpunit`:

```bash
--display-warnings
--display-deprecations
--display-notices
--display-errors
--display-failures
--debug
```

### Special Cases (zmsbackend & zmsclient)

**zmsclient:**

For `zmsclient` you need the php base image which starts a local mock server. This json in the mocks must match the signature the entity returned in the requests (usually this is the issue whenever tests fail in `zmsclient`).

**Using Docker:**

```bash
cd zmsclient
docker compose down && docker compose up -d && docker exec zmsclient-test-1 ./vendor/bin/phpunit
```

**Using Podman:**

```bash
cd zmsclient
./zmsclient-test
./zmsclient-test --filter "testSetKeyBasic"
```

The `zmsclient-test` script automatically detects and uses Docker or Podman, restarts containers for clean state, and runs PHPUnit tests.

#### Traditional Method (overwrites local DB)

For **zmsbackend**, test data must be imported. Please note that this will overwrite your local database.

**zmsbackend** (unified REST API and database module for `/terminvereinbarung/api/2`):

Using DDEV or Podman:

```bash
./zmsbackend/zmsbackend-test
```

Or manually (inside `zms-web` / `ddev ssh`):

```bash
cd zmsbackend && bin/importTestData --commit
cd zmsbackend && bin/configure && ./vendor/bin/phpunit
```

## PHP Containerized Unit Testing (Recommended - isolated environment)

Run your tests in clean, disposable containers to ensure they don’t affect your local system or database:

```bash
# Enter your web container
podman exec -it zms-web bash  # Podman
ddev ssh                      # DDEV

# Run zmsbackend tests
./zmsbackend/zmsbackend-test                    # Run all tests
./zmsbackend/zmsbackend-test --filter="StatusTest::testBasic"  # Run specific test
./zmsbackend/zmsbackend-test --filter="StatusGetTest::testRendering"  # Run specific test
```

**Available PHPUnit Flags:**

```bash
# Test Selection (filter is a regex matching against "Namespace\TestClass::testMethod")
--filter="TestClass::testMethod"  # Run specific test method
--filter="TestClass"              # Run all tests in a class
--filter="testMethod"             # Run all tests with matching method name
--filter="pattern"                # Run tests matching regex pattern

# Output & Verbosity
--verbose                         # More detailed output
--debug                           # Debug information
--stop-on-failure                 # Stop on first failure
--stop-on-error                   # Stop on first error
--stop-on-warning                 # Stop on first warning

# Coverage & Reports
--coverage-text                   # Text coverage report
--coverage-html=dir               # HTML coverage report
--coverage-clover=file.xml        # XML coverage report

# Test Execution
--group="groupName"               # Run tests in specific group
--exclude-group="groupName"       # Exclude tests in group
--testsuite="suiteName"           # Run specific test suite
```

**Examples:**

```bash
# Run specific test with verbose output
./zmsbackend/zmsbackend-test --filter="StatusTest::testBasic" --verbose

# Run all tests in a class and stop on first failure
./zmsbackend/zmsbackend-test --filter="StatusGetTest" --stop-on-failure

# Run tests with coverage report
./zmsbackend/zmsbackend-test --coverage-text

# Run tests excluding a specific group
./zmsbackend/zmsbackend-test --exclude-group="slow"
```

## zmscitizenview Unit Testing

Run frontend unit tests with Vitest/Jest setup from the module:

```bash
cd zmscitizenview
```

```bash
npm test
```

Filter by test name pattern:

```bash
npm test -- -t "AppointmentView"
```

## zmsautomation

`zmsautomation` is not a unit-test module. Its API/UI test suites are documented in [zmsautomation Documentation](./zmsautomation.md).

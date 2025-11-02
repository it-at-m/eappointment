# zmsautomation

Java REST-assured based API tests for ZMS APIs.

## Run locally

1. Ensure the target API under test is reachable (set BASE_URI accordingly), e.g.:
   - Local/dev: `http://localhost:8080`
   - DDEV/Docker: your exposed host/port for the API
2. Run tests:
   - `cd zmsautomation && mvn -q test -DBASE_URI=http://localhost:8080`

## Using docker-compose runner (recommended)

- One command to prepare DB and run tests:
  - `cd zmsautomation && ./zmsautomation-test` (defaults BASE_URI to `http://localhost:8080`)
  - Or set a different target: `BASE_URI=http://host.docker.internal:8080 ./zmsautomation-test`
- Reset environment:
  - `./zmsautomation-test --reset`

### Data preparation (optional)
The runner can import `.resources/zms.sql` and then run the ZMS hourly cron with Munich transformer:
- Set env and run:
  - `ZMS_CRONROOT=1 ZMS_SOURCE_DLDB_MUNICH="<munich-source-url>" ./zmsautomation-test`
This will:
- import `.resources/zms.sql` into the test MariaDB
- execute `zmsapi/cron/cronjob.hourly --city=munich` inside a PHP base container

## First test
- `zmsapi/StatusEndpointTest.java` hits `/terminvereinbarung/api/2/status/` and expects HTTP 200 with JSON containing `status`.

## Test data (future)
- `src/test/resources/db/01-base-schema.sql`: copy of base schema
- `src/test/resources/db/02-status-test-data.sql`: minimal data for status endpoint

## References
- REST-assured: https://github.com/rest-assured/rest-assured

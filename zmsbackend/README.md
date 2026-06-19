# zmsbackend

Unified REST API and database access module ([GH-2604](https://github.com/it-at-m/eappointment/issues/2604)). Runs **parallel** to `zmsapi` and `zmsdb` during migration.

## Status

All domains migrated from `zmsapi` / `zmsdb` into domain folders (`Api`, `Service`, `Repository`, `Exception`). `zmsapi` and `zmsdb` remain in the repo until cutover.

Re-run migration from sources:

```bash
php zmsbackend/bin/migrate_all.php --force
```

## Setup

```bash
cd zmsbackend
cp config.example.php config.php   # adjust DB credentials
composer install
```

Run the full test workflow (database, fixtures, phpunit):

```bash
./zmsbackend/zmsbackend-test
# inside container:
podman exec -it zms-web bash -lc "./zmsbackend/zmsbackend-test"
```

## Run API (local)

Point your web server at `zmsbackend/public/` (same pattern as `zmsapi`).

OpenAPI documentation: https://it-at-m.github.io/eappointment/zmsbackend/public/doc/index.html

Legacy `zmsapi` docs remain at https://it-at-m.github.io/eappointment/zmsapi/public/doc/index.html during migration.

## Tests

```bash
./zmsbackend/zmsbackend-test
vendor/bin/phpunit   # after DB/fixtures are set up manually
```

Tests mirror source layout under `tests/Zmsbackend/<Domain>/`.

## Layout

```
src/Zmsbackend/
  Application.php
  Connection/
  Query/              # shared query builder (cross-cutting)
  Api/                # BaseController, Index, Response
  Helper/
  Availability/
    Api/              # HTTP controllers
    Service/          # business logic (former zmsdb services)
    Repository/       # SQL/query code (former zmsdb Query/*)
    Exception/
```

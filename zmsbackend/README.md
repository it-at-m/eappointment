# zmsbackend

Unified REST API and database access module ([GH-2604](https://github.com/it-at-m/eappointment/issues/2604)). Replaces the former `zmsapi` and `zmsdb` modules.

## Status

All domains from `zmsapi` / `zmsdb` live in domain folders (`Api`, `Service`, `Repository`, `Exception`).

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

Point your web server at `zmsbackend/public/`.

OpenAPI documentation: https://it-at-m.github.io/eappointment/zmsbackend/public/doc/index.html

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
    Service/          # business logic
    Repository/       # SQL/query code
    Exception/
```

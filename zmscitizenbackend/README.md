# zmscitizenbackend

Standalone Citizen API backend (RefArch name). Introduced **side-by-side** with live [`zmscitizenapi`](../zmscitizenapi) so schemas, database access, and repositories can move here without churning the serving module.

Tracked in [it-at-m/eappointment#2899](https://github.com/it-at-m/eappointment/issues/2899).

## Status

`zmscitizenapi` still serves traffic. This package is the parallel refactor target: public JSON contract stays compatible until an intentional cutover (gateway / `zmscitizenview` / deploy). After cutover, `zmscitizenapi` is retired. The Spring Boot phase keeps the `zmscitizenbackend` name.

Citizen JSON schemas and model validation live in this module (`schema/citizenapi` + `BO\Zmscitizenbackend\Schema\Entity`). They do not load from `zmsentities` schema paths.

## Layout

| Path | Role |
|------|------|
| `src/Zmscitizenbackend/` | PSR-4 namespace `BO\Zmscitizenbackend\` |
| `src/Zmscitizenbackend/Schema/` | JSON Schema loader, Entity base, and opis-based validator |
| `src/Zmscitizenbackend/Models/` | Citizen models hydrated against local schemas |
| `schema/citizenapi/` | Citizen JSON schemas (owned here; `zmsentities/schema/citizenapi` remains for live `zmscitizenapi` until cutover) |
| `tests/Zmscitizenbackend/` | PHPUnit suite |

## Tests

```bash
composer install
./vendor/bin/phpunit -c phpunit.xml
```

# zmscitizenbackend

Standalone Citizen API backend (RefArch name). Introduced **side-by-side** with live [`zmscitizenapi`](../zmscitizenapi) so schemas, database access, and repositories can move here without churning the serving module.

Tracked in [it-at-m/eappointment#2899](https://github.com/it-at-m/eappointment/issues/2899).

## Status

`zmscitizenapi` still serves traffic. This package is the parallel refactor target: public JSON contract stays compatible until an intentional cutover (gateway / `zmscitizenview` / deploy). After cutover, `zmscitizenapi` is retired. The Spring Boot phase keeps the `zmscitizenbackend` name.

## Layout

| Path | Role |
|------|------|
| `src/Zmscitizenbackend/` | PSR-4 namespace `BO\Zmscitizenbackend\` |
| `tests/Zmscitizenbackend/` | PHPUnit suite |

## Tests

```bash
composer install
./vendor/bin/phpunit -c phpunit.xml
```

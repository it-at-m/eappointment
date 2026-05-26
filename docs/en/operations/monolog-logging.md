# Monolog logging (zmsslim)

ZMS applications use a single PSR-3 logger on the global `App` class: **`App::$log`**. It is configured in [`zmsslim`](https://github.com/it-at-m/eappointment/tree/main/zmsslim) by `BO\Slim\Bootstrap` and shared by all Slim-based modules (`zmsapi`, `zmsadmin`, `zmscitizenapi`, …).

## Quick reference

| Topic | Detail |
| --- | --- |
| Logger property | `App::$log` (`Monolog\Logger`, `null` before bootstrap) |
| Minimum level | `App::DEBUGLEVEL` ← env `DEBUGLEVEL` ← default `INFO` (constant `ZMS_DEBUGLEVEL` in zmsslim) |
| Web bootstrap | `\BO\Slim\Bootstrap::init()` |
| CLI / cron | `\BO\Slim\Bootstrap::ensureLogger()` or `initForCli()` via `script_bootstrap.php` |
| Output | JSON lines to **stderr** (web) or **stdout** (CLI/cron) |
| Do not use | PHP `error_log()`, `print_r()`, `echo` for application logging |

## Log levels

`DEBUGLEVEL` / `App::DEBUGLEVEL` sets the **minimum** level written by Monolog. Messages below that level are dropped.

| `DEBUGLEVEL` env / constant | Monolog constant | Typical use in ZMS |
| --- | --- | --- |
| `DEBUG` | `Logger::DEBUG` | Verbose diagnostics (mail payloads, cache details) |
| `INFO` | `Logger::INFO` | Normal operations (login, cron progress, cache hits) |
| `NOTICE` | `Logger::NOTICE` | Notable but expected events |
| `WARNING` | `Logger::WARNING` | Recoverable problems (rate limits, skipped entities) |
| `ERROR` | `Logger::ERROR` | Failures that need attention |
| `CRITICAL` | `Logger::CRITICAL` | Severe errors (unhandled exceptions in Twig handler) |
| `ALERT` | `Logger::ALERT` | Rare; same scale as Monolog |
| `EMERGENCY` | `Logger::EMERGENCY` | Rare; same scale as Monolog |

Implementation mapping lives in `zmsslim/src/Slim/Bootstrap.php` (`$debuglevels` and `parseDebugLevel()`).

### Environment variable

```bash
# .env / deployment / DDEV
DEBUGLEVEL=INFO
```

`zmsslim/src/Slim/Application.php` defines:

```php
define('ZMS_DEBUGLEVEL', getenv('DEBUGLEVEL') ? getenv('DEBUGLEVEL') : 'INFO');
const DEBUGLEVEL = ZMS_DEBUGLEVEL;
```

Each module’s `class App extends \BO\…\Application` inherits this. Override in `config.php` only for local experiments, e.g. `const DEBUGLEVEL = 'WARNING';`.

Invalid values fall back to **DEBUG** (permissive) in `parseDebugLevel()`.

## How to log

After `bootstrap.php` or `script_bootstrap.php`:

```php
\App::$log->info('Login successful', [
    'account' => $accountName,
]);

\App::$log->warning('Could not remove availability', [
    'availabilityId' => $availabilityId,
]);

\App::$log->error('SQL import failed', [
    'file' => basename($file),
    'exception' => $e->getMessage(),
]);
```

Use **lowercase** PSR-3 method names: `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`.

### Context arrays

Prefer structured context (second argument) over string concatenation. The JSON processor adds standard fields:

- `time_local`, `client_ip`, `remote_addr`
- `application` (`App::IDENTIFIER`)
- `module` (`App::MODULE_NAME`)
- `cron`, `cron_name` when `ZMS_CRON_LOG` / `ZMS_CRON_NAME` are set in cron shell scripts

### Cron logging

Cron entrypoints export `ZMS_CRON_LOG=1` and `ZMS_CRON_NAME=zmsapi_hourly` (example). `Bootstrap::isCronLogging()` adds searchable `cron` / `cron_name` fields to each JSON line.

Helpers such as `zmsdb`’s `VerboseCronLogTrait` use `Bootstrap::normalizeLogLevelName()` for configurable cron verbosity.

### Libraries without full bootstrap

Shared packages (`zmsdldb`, `zmsclient`) may run without `App`. Use optional logging only when the class exists:

```php
if (class_exists('\App', false) && isset(\App::$log)) {
    \App::$log->error('…', ['context' => $value]);
}
```

Do **not** use `isset(\App::$log)` alone — PHP will fatal if `App` is not loaded.

## JSON log shape (example)

```json
{
  "time_local": "2026-05-26T12:00:00+02:00",
  "client_ip": "127.0.0.1",
  "application": "zmsapi",
  "module": "zmsapi",
  "cron": true,
  "cron_name": "zmsapi_hourly",
  "message": "Migration check finished",
  "level": "INFO",
  "context": { "pending": 0 },
  "extra": []
}
```

## Repository log inventory

The table below is **generated automatically** from `App::$log->…` calls in module PHP sources (excluding `vendor/` and `tests/`). Regenerate locally:

```bash
cd docs && npm run docs:log-inventory
```

It updates when you run `npm run docs:dev` or `docs:build` (VitePress config runs the generator first).

<LogInventory />

## Related code

- `zmsslim/src/Slim/Application.php` — `public static $log`
- `zmsslim/src/Slim/Bootstrap.php` — `configureLogger()`, `ensureLogger()`, `normalizeLogLevelName()`
- `zmsslim/README.md` — Slim bootstrap overview

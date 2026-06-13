# Monolog logging (zmsslim)

ZMS applications use a single PSR-3 logger on the global `App` class: **`App::$log`**. It is configured in [`zmsslim`](https://github.com/it-at-m/eappointment/tree/main/zmsslim) by `BO\Slim\Bootstrap` and shared by all Slim-based modules (`zmsapi`, `zmsadmin`, `zmscitizenapi`, …).

The **minimum log level** is also centralized in zmsslim: you set **`DEBUGLEVEL`** once in the environment; zmsslim exposes it as **`ZMS_DEBUGLEVEL`**, and every module’s `App::DEBUGLEVEL` inherits that value for `Bootstrap::configureLogger()`.

## Quick reference

| Topic                   | Detail                                                                                |
| ----------------------- | ------------------------------------------------------------------------------------- |
| Logger property         | `App::$log` (`Monolog\Logger`, `null` before bootstrap)                               |
| Env variable            | `DEBUGLEVEL` (e.g. in `.env`, DDEV, deployment) — default `INFO`                      |
| zmsslim define          | `ZMS_DEBUGLEVEL` — set in `Application.php` from `getenv('DEBUGLEVEL')`               |
| App constant            | `App::DEBUGLEVEL` — each module inherits `ZMS_DEBUGLEVEL` from `\BO\Slim\Application` |
| Effective minimum level | Whichever of the above applies after bootstrap (`App::DEBUGLEVEL` at runtime)         |
| Web bootstrap           | `\BO\Slim\Bootstrap::init()`                                                          |
| CLI / cron              | `\BO\Slim\Bootstrap::ensureLogger()` or `initForCli()` via `script_bootstrap.php`     |
| Output                  | JSON lines to **stderr** (web) or **stdout** (CLI/cron)                               |
| Do not use              | PHP `error_log()`, `print_r()`, `echo` for application logging                        |

## Central debug level (`ZMS_DEBUGLEVEL`)

zmsslim owns the debug-level wiring for **all** Slim modules. You do not configure a separate log level per module in production; one environment variable applies everywhere that bootstraps through `\BO\Slim\Bootstrap`.

```mermaid
flowchart LR
  env["DEBUGLEVEL env"]
  zms["ZMS_DEBUGLEVEL\n(zmsslim Application.php)"]
  app["App::DEBUGLEVEL\n(each module App)"]
  boot["Bootstrap::configureLogger()"]
  mono["Monolog minimum level\n(App::$log)"]

  env --> zms --> app --> boot --> mono
```

1. **Operations** set `DEBUGLEVEL` (for example `INFO` or `WARNING`) in `.env`, DDEV, or deployment config.
2. When `zmsslim/src/Slim/Application.php` is loaded, it defines **`ZMS_DEBUGLEVEL`** from that env var (default `INFO` if unset).
3. `\BO\Slim\Application` declares **`const DEBUGLEVEL = ZMS_DEBUGLEVEL`**. Each module’s `class App extends \BO\Zmsapi\Application` (etc.) inherits the same constant unless you override it locally.
4. On bootstrap, **`Bootstrap::init()`** / **`ensureLogger()`** / **`initForCli()`** call **`configureLogger(App::DEBUGLEVEL, App::IDENTIFIER)`**. The level is shared; only **`App::IDENTIFIER`** and **`App::MODULE_NAME`** differ per module in the JSON output.

So **`ZMS_DEBUGLEVEL` is the single zmsslim source of truth** for how verbose logging is across zmsapi, zmsadmin, zmscitizenapi, zmsmessaging, cron scripts, and the other Slim apps.

**What you configure:** `DEBUGLEVEL` in the environment (not a separate `ZMS_DEBUGLEVEL` env name).

**What code reads at runtime:** `App::DEBUGLEVEL` (backed by `ZMS_DEBUGLEVEL`).

**Rare override:** a module `config.php` may redefine `const DEBUGLEVEL = 'WARNING';` for local experiments only — avoid this in shared deployment config.

**CLI fallback:** if `App::DEBUGLEVEL` is not defined yet, `Bootstrap::initForCli()` reads `getenv('DEBUGLEVEL')` directly.

## Log levels

`App::DEBUGLEVEL` (from `ZMS_DEBUGLEVEL`) sets the **minimum** level written by Monolog. Messages below that level are dropped.

| `DEBUGLEVEL` env / constant | Monolog constant    | Typical use in ZMS                                   |
| --------------------------- | ------------------- | ---------------------------------------------------- |
| `DEBUG`                     | `Logger::DEBUG`     | Verbose diagnostics (mail payloads, cache details)   |
| `INFO`                      | `Logger::INFO`      | Normal operations (login, cron progress, cache hits) |
| `NOTICE`                    | `Logger::NOTICE`    | Notable but expected events                          |
| `WARNING`                   | `Logger::WARNING`   | Recoverable problems (rate limits, skipped entities) |
| `ERROR`                     | `Logger::ERROR`     | Failures that need attention                         |
| `CRITICAL`                  | `Logger::CRITICAL`  | Severe errors (unhandled exceptions in Twig handler) |
| `ALERT`                     | `Logger::ALERT`     | Rare; same scale as Monolog                          |
| `EMERGENCY`                 | `Logger::EMERGENCY` | Rare; same scale as Monolog                          |

Implementation mapping lives in `zmsslim/src/Slim/Bootstrap.php` (`$debuglevels` and `parseDebugLevel()`).

### Example configuration

```bash
# .env / deployment / DDEV — applies to all Slim modules via zmsslim
DEBUGLEVEL=INFO
```

Definition in `zmsslim/src/Slim/Application.php`:

```php
define('ZMS_DEBUGLEVEL', getenv('DEBUGLEVEL') ? getenv('DEBUGLEVEL') : 'INFO');
const DEBUGLEVEL = ZMS_DEBUGLEVEL;
```

Invalid values fall back to **DEBUG** (permissive) in `Bootstrap::parseDebugLevel()`.

## Per-module HTTP request logging

Unlike **`DEBUGLEVEL`** (one value for all Slim modules), **HTTP request/response logging** is configured **per module** via `ZMS_<MODULE>_LOGGER_*` environment variables — the same naming pattern as `ZMS_ADMIN_TWIG_CACHE`, `ZMS_API_TWIG_CACHE`, and so on.

Modules that register `RequestLoggingMiddleware` (via `BO\Slim\Helper\ModuleLoggerInitializer` or their own bootstrap) emit one structured **`HTTP Request`** line per handled request through `BO\Slim\LoggerService::logRequest()` → `App::$log`.

### Request log throttling only

`…_LOGGER_MAX_REQUESTS` and `…_LOGGER_MAX_ERROR_REQUESTS` are **access-log throttles**. They cap how many **`HTTP Request`** lines `LoggerService::logRequest()` writes per time window. They do **not** limit general application logging.

| Logging path                                                     | Throttled by `LOGGER_MAX_*`?                 | Controlled by                            |
| ---------------------------------------------------------------- | -------------------------------------------- | ---------------------------------------- |
| `HTTP Request` (status &lt; 400)                                 | Yes — `…_LOGGER_MAX_REQUESTS`                | Per-module env + `…_LOGGER_CACHE_TTL`    |
| `HTTP Request` (status ≥ 400)                                    | Only if `…_LOGGER_MAX_ERROR_REQUESTS` &gt; 0 | Per-module env (default `0` = unlimited) |
| `LoggerService::logError()` (exceptions)                         | No                                           | —                                        |
| `LoggerService::logWarning()` / `logInfo()`                      | No                                           | —                                        |
| Direct `App::$log->info()` / `warning()` / `error()` in app code | No                                           | `DEBUGLEVEL`                             |

So: **`DEBUGLEVEL`** sets how verbose application logs are globally; **`LOGGER_MAX_*`** only prevents high-frequency modules (especially calldisplay and ticket printers) from flooding logs with routine successful requests.

Successful and failed request logs use **separate counters** and the same window length (`…_LOGGER_CACHE_TTL`, default 60 seconds). Throttling a successful poll does not block logging a later 500 on the same module.

| Module           | Env prefix                   | Typical traffic                     |
| ---------------- | ---------------------------- | ----------------------------------- |
| zmscitizenapi    | `ZMS_CITIZENAPI_LOGGER_*`    | Public booking API                  |
| zmsapi           | `ZMS_API_LOGGER_*`           | Internal REST API                   |
| zmsadmin         | `ZMS_ADMIN_LOGGER_*`         | Staff UI                            |
| zmscalldisplay   | `ZMS_CALLDISPLAY_LOGGER_*`   | Display monitors (frequent polling) |
| zmsstatistic     | `ZMS_STATISTIC_LOGGER_*`     | Statistics UI                       |
| zmsticketprinter | `ZMS_TICKETPRINTER_LOGGER_*` | Ticket printers (frequent polling)  |

### LoggerService variables

| Variable                                        | Default        | Role                                                                                      |
| ----------------------------------------------- | -------------- | ----------------------------------------------------------------------------------------- |
| `…_LOGGER_MAX_REQUESTS`                         | `1000`         | Max successful `HTTP Request` lines (status &lt; 400) per rate-limit window (`CACHE_TTL`) |
| `…_LOGGER_MAX_ERROR_REQUESTS`                   | `0`            | Max failed `HTTP Request` lines (status ≥ 400) per window; `0` = unlimited                |
| `…_LOGGER_RESPONSE_LENGTH`                      | `1048576`      | Max response body bytes considered when logging errors                                    |
| `…_LOGGER_STACK_LINES`                          | `20`           | Stack trace lines on logged exceptions                                                    |
| `…_LOGGER_MESSAGE_SIZE`                         | `8192`         | Max size of a single log message                                                          |
| `…_LOGGER_CACHE_TTL`                            | `60`           | Rate-limit window in seconds (uses `CACHE_DIR`)                                           |
| `…_LOGGER_MAX_RETRIES`                          | `3`            | Cache lock retries for rate limiting                                                      |
| `…_LOGGER_BACKOFF_MIN` / `…_LOGGER_BACKOFF_MAX` | `100` / `1000` | Backoff between retries (ms)                                                              |
| `…_LOGGER_LOCK_TIMEOUT`                         | `5`            | Cache lock timeout (seconds)                                                              |

See `.ddev/.env.template` / `.devcontainer/.env.template` for full examples per module.

### Tuning high-frequency modules

**zmscalldisplay** and **zmsticketprinter** are special: every monitor or ticket printer typically polls the server **every few seconds**. With default `LOGGER_MAX_REQUESTS=1000`, a handful of devices can produce large, repetitive log volume even at `DEBUGLEVEL=INFO`.

For those modules, consider **lowering** `ZMS_CALLDISPLAY_LOGGER_MAX_REQUESTS` and/or `ZMS_TICKETPRINTER_LOGGER_MAX_REQUESTS` so routine poll traffic does not dominate your log stream. Admin, API, and citizen modules usually keep the defaults.

```bash
# Example: cap display/printer poll logging without affecting other modules
ZMS_CALLDISPLAY_LOGGER_MAX_REQUESTS=120
ZMS_TICKETPRINTER_LOGGER_MAX_REQUESTS=120

# Other modules can stay at the template default (1000)
ZMS_ADMIN_LOGGER_MAX_REQUESTS=1000
ZMS_API_LOGGER_MAX_REQUESTS=1000
```

Lowering `…_LOGGER_MAX_REQUESTS` throttles only **successful** `HTTP Request` lines (Monolog `info`, status &lt; 400). Failed requests (status ≥ 400, Monolog `error`) use `…_LOGGER_MAX_ERROR_REQUESTS` instead; the default `0` means no cap.

These variables do not affect exceptions, warnings, info messages from other `LoggerService` methods, or direct `App::$log->…` calls elsewhere in the codebase.

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

It updates when you run `npm run docs:dev` or `docs:build` (VitePress config runs the generator first). Use the dropdown filters, search box, or **click a column header** to sort (toggle ascending/descending).

<LogInventory />

## Related

- [Monitoring and status](./monitoring-and-status.md) — `GET /status/` metrics and Grafana

## Related code

- `zmsslim/src/Slim/Application.php` — `ZMS_DEBUGLEVEL`, `DEBUGLEVEL`, `public static $log`
- `zmsslim/src/Slim/Bootstrap.php` — `configureLogger()`, `ensureLogger()`, `normalizeLogLevelName()`
- `zmsslim/src/Slim/LoggerService.php` — HTTP request logging, rate limiting
- `zmsslim/src/Slim/Helper/ModuleLoggerInitializer.php` — per-module logger env wiring and middleware registration
- `zmsslim/README.md` — Slim bootstrap overview

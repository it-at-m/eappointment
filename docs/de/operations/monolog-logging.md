# Monolog-Logging (zmsslim)

ZMS-Anwendungen nutzen einen gemeinsamen PSR-3-Logger auf der globalen `App`-Klasse: **`App::$log`**. Konfiguriert wird er in [`zmsslim`](https://github.com/it-at-m/eappointment/tree/main/zmsslim) durch `BO\Slim\Bootstrap` und von allen Slim-Modulen (`zmsapi`, `zmsadmin`, `zmscitizenapi`, …) geteilt.

## Kurzreferenz

| Thema | Detail |
| --- | --- |
| Logger-Property | `App::$log` (`Monolog\Logger`, vor Bootstrap `null`) |
| Mindest-Level | `App::DEBUGLEVEL` ← Umgebung `DEBUGLEVEL` ← Standard `INFO` (Konstante `ZMS_DEBUGLEVEL` in zmsslim) |
| Web-Bootstrap | `\BO\Slim\Bootstrap::init()` |
| CLI / Cron | `\BO\Slim\Bootstrap::ensureLogger()` oder `initForCli()` über `script_bootstrap.php` |
| Ausgabe | JSON-Zeilen auf **stderr** (Web) oder **stdout** (CLI/Cron) |
| Nicht verwenden | PHP `error_log()`, `print_r()`, `echo` für Anwendungs-Logs |

## Log-Level

`DEBUGLEVEL` / `App::DEBUGLEVEL` legt das **Mindest-Level** für Monolog fest. Nachrichten darunter werden verworfen.

| `DEBUGLEVEL` Env / Konstante | Monolog-Konstante | Typische Nutzung in ZMS |
| --- | --- | --- |
| `DEBUG` | `Logger::DEBUG` | Ausführliche Diagnose (Mail-Payloads, Cache) |
| `INFO` | `Logger::INFO` | Normaler Betrieb (Login, Cron-Fortschritt, Cache-Treffer) |
| `NOTICE` | `Logger::NOTICE` | Beachtenswert, aber erwartbar |
| `WARNING` | `Logger::WARNING` | Behebbare Probleme (Rate Limits, übersprungene Entitäten) |
| `ERROR` | `Logger::ERROR` | Fehler mit Handlungsbedarf |
| `CRITICAL` | `Logger::CRITICAL` | Schwere Fehler (z. B. Twig-Exception-Handler) |
| `ALERT` | `Logger::ALERT` | Selten; Monolog-Skala |
| `EMERGENCY` | `Logger::EMERGENCY` | Selten; Monolog-Skala |

Die Zuordnung steht in `zmsslim/src/Slim/Bootstrap.php` (`$debuglevels`, `parseDebugLevel()`).

### Umgebungsvariable

```bash
DEBUGLEVEL=INFO
```

In `zmsslim/src/Slim/Application.php`:

```php
define('ZMS_DEBUGLEVEL', getenv('DEBUGLEVEL') ? getenv('DEBUGLEVEL') : 'INFO');
const DEBUGLEVEL = ZMS_DEBUGLEVEL;
```

Ungültige Werte fallen in `parseDebugLevel()` auf **DEBUG** zurück.

## Logging im Code

Nach `bootstrap.php` oder `script_bootstrap.php`:

```php
\App::$log->info('Login successful', ['account' => $accountName]);
\App::$log->warning('Could not remove availability', ['availabilityId' => $id]);
\App::$log->error('SQL import failed', ['exception' => $e->getMessage()]);
```

PSR-3-Methoden in **Kleinbuchstaben**: `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`.

### Kontext-Arrays

Strukturierten Kontext (zweites Argument) bevorzugen. Zusätzliche Felder u. a.: `application`, `module`, bei Cron `cron` / `cron_name` (`ZMS_CRON_LOG`, `ZMS_CRON_NAME`).

### Bibliotheken ohne Bootstrap

Optional nur mit Prüfung:

```php
if (class_exists('\App', false) && isset(\App::$log)) {
    \App::$log->error('…', ['context' => $value]);
}
```

## Log-Inventar im Repository

Die folgende Tabelle wird **automatisch** aus `App::$log->…` in Modul-PHP-Quellen erzeugt (ohne `vendor/` und `tests/`). Lokal aktualisieren:

```bash
cd docs && npm run docs:log-inventory
```

Aktualisierung auch bei `npm run docs:dev` / `docs:build`.

<LogInventory />

## Verwandter Code

- `zmsslim/src/Slim/Application.php` — `public static $log`
- `zmsslim/src/Slim/Bootstrap.php` — Logger-Konfiguration
- `zmsslim/README.md` — Slim-Bootstrap-Übersicht

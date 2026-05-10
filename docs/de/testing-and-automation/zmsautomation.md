# zmsautomation-Dokumentation

## ZMS Automation – ATAF-Integration

Dieses Modul enthält API- und UI-Tests für ZMS auf Basis von ATAF (Test Automation Framework) und Cucumber.
`zmsautomation` baut auf [it-at-m/agile-test-automation-framework](https://github.com/it-at-m/agile-test-automation-framework) auf.

## Voraussetzungen

- Java 21
- Maven 3.9+
- MySQL-/MariaDB-Datenbank (für lokale Tests)
- Zugriff auf Maven Central (ATAF-Artefakte werden unter `de.muenchen.ataf:core|rest|web` veröffentlicht)

## Projektstruktur

- `src/test/java/zms/api/` – ursprüngliche REST-assured + JUnit-Tests (Standalone-Profil)
- `src/test/java/zms/ataf/`
  - `zms/ataf/rest/steps/` – REST-Step-Definitionen (REST Assured)
  - `zms/ataf/ui/steps/` – UI-Step-Definitionen (Selenium/ATAF web)
  - `zms/ataf/ui/pages/**` – Page-Objects für Admin, Statistik, Buergeransicht, Mailinator
- `src/test/resources/features/` – Cucumber-Feature-Dateien
  - `rest/zmsapi/` – Features der ZMS-REST-API
  - `rest/zmscitizenapi/` – Features der Citizen-REST-API
  - `ui/zmsadmin/` – Admin-UI-Features
  - `ui/buergeransicht/` – veraltete Buergeransicht-UI-Features aus `it-at-m/eappointment-buergeransicht` (nicht für `zmscitizenview` verwendet)
  - `ui/zmsstatistic/` – Statistik-UI-Features
  - `ui/zmscitizenview/` – CitizenView-UI (Service Finder + vollständige Buchung E2E)
- `src/main/resources/db/migration/` – Flyway-Datenbankmigrationen

## Tests ausführen

### Mit dem Test-Skript (empfohlen)

Das Skript `zmsautomation-test` kümmert sich um Datenbank-Setup, Migrationen und Testausführung.

```bash
# alle ATAF-Tests ausführen (API + UI)
./zmsautomation/zmsautomation-test -Pataf-api -Pataf-ui

# Tests mit bestimmten Tags ausführen (mit @ignore markierte Szenarien sind ausgeschlossen, sofern @ignore nicht im Ausdruck enthalten ist)
./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@smoke"
# inklusive ignorierter Szenarien, z. B.:
# ./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@ignore and @web"

# spezifisches API-Feature ausführen (Pfad relativ zum zmsautomation-Modul)
./zmsautomation/zmsautomation-test -Pataf-api -Dcucumber.features="src/test/resources/features/rest/zmsapi/status.feature"

# nur API-Tests (kein Selenium)
./zmsautomation/zmsautomation-test -Pataf-api

# nur UI-Tests (Selenium/ATAF web)
./zmsautomation/zmsautomation-test -Pataf-ui
```

Das Skript:

1. Sichert die Datenbank
2. Leert Caches
3. Setzt die Datenbank zurück (Tabellen verwerfen)
4. Importiert die Basis-Datenbank (`.resources/zms.sql`)
5. Führt Flyway-Migrationen aus (Maven-Plugin)
6. Führt PHP-Migrationen aus (`zmsapi` migrate)
7. Führt den Stunden-Cron aus (mit Wiederholungen)
8. Führt den Minuten-Cron und die Slot-Berechnung aus (`calculateSlots`)
9. Führt HTTP-Health-Checks aus (zmsapi, Citizen-API, CitizenView, optionales refarch-gateway)
10. Richtet Display-/Browser-Tooling ein (Xvfb, Treiberprüfungen)
11. Führt `mvn test` mit deinen Argumenten aus (Standard-Tag-Filter ergänzt `not @ignore`, sofern du `@ignore` nicht selbst einbeziehst)
12. Druckt Test-Reports
13. Leert Caches erneut
14. Stellt Datenbank- und Keycloak-JSON-Backups wieder her (sofern nicht `SKIP_DB_RESTORE=1`)
15. Bereinigt abschließend (Datenverzeichnis, Flyway-Test-Zeilen usw.; auch auf `EXIT` registriert)

### Standalone-Profil (klassische REST-assured-Tests)

Führt die ursprünglichen JUnit-basierten REST-assured-Tests aus:

```bash
cd zmsautomation
mvn test -Pstandalone
```

### ATAF als Standard

Das Maven-Profil `ataf` ist in diesem Modul standardmäßig aktiv, daher binden `mvn test` und `mvn test-compile` bereits die ATAF-/Cucumber-/TestNG-/Selenium-Abhängigkeiten ein.

### ATAF-Profile (lokale Entwicklung)

- Alle ATAF-Tests ausführen (API + UI):

```bash
cd zmsautomation
mvn test
```

- Nur API-Tests (REST Assured, kein Selenium):

```bash
mvn test -Pataf-api
# optionaler Filter:
# mvn test -Pataf-api -Dcucumber.filter.tags="@rest"
# mvn test -Pataf-api -Dcucumber.filter.tags="@zmsapi"
# mvn test -Pataf-api -Dcucumber.filter.tags="@zmscitizenapi"
```

- Nur UI-Tests (Selenium/ATAF web, kein REST Assured):

```bash
mvn test -Pataf-ui
# optionaler Filter:
# mvn test -Pataf-ui -Dcucumber.filter.tags="@web"
# mvn test -Pataf-ui -Dcucumber.filter.tags="@zmsadmin"
# mvn test -Pataf-ui -Dcucumber.filter.tags="@buergeransicht"
# mvn test -Pataf-ui -Dcucumber.filter.tags="@zmsstatistic"
# mvn test -Pataf-ui -Dcucumber.filter.tags="@zmscitizenview"
```

### macOS-Host (CLI)

Vom Repo-Root:

```bash
./cli tests install-mac-deps
```

Für Safari zusätzlich aktivieren: Safari → Entwickeln → Automatisierung erlauben.

```bash
./cli tests run-mac-local --db-full-setup
./cli tests run-mac-local
./cli tests trust-local-gateway
```

Verwende `--browser safari` (oder `chrome`, `firefox`, `edge`, `safari`) nach Bedarf.

## Umgebungsvariablen

### API-Endpunkte

- `BASE_URI` – Basis der ZMS-API
- `CITIZEN_API_BASE_URI` – Basis der Citizen-API (REST-Steps rufen zms-web direkt auf)
- `ADMIN_BASE_URI` / `STATISTIC_BASE_URI` – Basen von Admin/Statistik
- `CITIZEN_VIEW_BASE_URI` / `CITIZENVIEW_PORT` – CitizenView/Vite-Server (Standard `8082`)
- `REFARCH_GATEWAY_OFFICES_URL` – optionale URL für den Gateway-Health-Ping
- `SKIP_REFARCH_GATEWAY_HEALTH=1` – Gateway-Ping überspringen

### Datenbankkonfiguration

- `MYSQL_HOST` (Standard `db`)
- `MYSQL_PORT` (Standard `tcp://db:3306`)
- `MYSQL_DATABASE` (Standard `db`)
- `MYSQL_USER` (Standard `db`)
- `MYSQL_PASSWORD` (Standard `db`)
- `MYSQL_ROOT_PASSWORD` (Standard `root`)

### UI-Tests (SSO)

Für lokale UI-Tests (Statistik, Admin) ist der Standard-SSO-Benutzer der Keycloak-Account `ataf` (Passwort `vorschau`) aus den Keycloak-Migrationsdaten.

## Beispiel

```bash
export BASE_URI="http://web/terminvereinbarung/api/2"
export CITIZEN_API_BASE_URI="http://web/terminvereinbarung/api/citizen"
export MYSQL_HOST="db"
export MYSQL_PORT="tcp://db:3306"
export MYSQL_DATABASE="db"
export MYSQL_USER="db"
export MYSQL_PASSWORD="db"

cd zmsautomation && mvn test
```

## Datenbankeinrichtung

ATAF-Tests führen vor der Testausführung automatisch Flyway-Migrationen aus. Die Migrationen liegen unter `src/main/resources/db/migration/`.

## Beispiele für Test-Tags

- API-Tags:
  - `@rest`
  - `@zmsapi`
  - `@zmscitizenapi`
- UI-Tags:
  - `@web`
  - `@zmsadmin`
  - `@buergeransicht` (veraltet; nicht für `zmscitizenview` verwendet)
  - `@zmsstatistic`
  - `@zmscitizenview`
  - `@jumpin`
  - `@ruppertstrasse`
  - `@passkalender`
  - `@hauptkalender`
  - `@abholung`
  - `@executeLocally`
  - `@allowDisabledServicesMix`
- Sonstige:
  - `@smoke`

`@executeLocally` ist ein reiner UI-Tag (`@web`-Szenarien), nicht für reine REST-Szenarien.

## Feature-Dateien

### API-Features (`src/test/resources/features/rest/`)

- `rest/zmsapi/status.feature` – Tests des Status-Endpoints
- `rest/zmscitizenapi/zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links_citizenapi.feature` – Buchungs-Flow der Citizen-API

### UI-Features (`src/test/resources/features/ui/`)

- `ui/zmsadmin/` – Admin-UI-Features
- `ui/buergeransicht/` – veraltete Buergeransicht-Features aus `it-at-m/eappointment-buergeransicht` (nicht für `zmscitizenview` verwendet)
- `ui/zmscitizenview/` – Buchungs-UI-Features von CitizenView
- `ui/zmsstatistic/` – Statistik-UI-Features

## zmsautomation in GitHub-Workflows

GitHub-Actions-Workflow: `.github/workflows/zmsautomation-workflow.yaml`.

Dokumentation folgt.

## zmsautomation in Safari unter macOS außerhalb des Containers

Safari-basierte Automatisierung kann unter macOS bereits außerhalb des Containers ausgeführt werden.
Aktuell ist das für Safari erforderlich, da es keinen Safari-WebDriver-Runtime für Linux-ARM/-AMD-Container gibt.

In `cli_test.py` steht ein CLI-Setup-/-Run-Flow zur Verfügung:

```bash
# lokale macOS-Testabhängigkeiten installieren (inkl.: sudo safaridriver --enable)
./cli tests install-mac-deps

# lokale Tests auf macOS mit Safari ausführen
./cli tests run-mac-local --browser safari
```

In Safari musst du außerdem aktivieren:

- `Safari → Entwickeln → Automatisierung erlauben`

## Migrationshinweise

- `zmsautomation` nutzt ATAF + Cucumber; CI-/Workflow-Umgebungen können separat festgepinnt werden.

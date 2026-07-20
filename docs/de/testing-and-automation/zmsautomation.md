# zmsautomation-Dokumentation

## ZMS Automation – [ATAF](https://it-at-m.github.io/agile-test-automation-framework/)-Integration

Dieses Modul enthält API- und UI-Tests für ZMS auf Basis von [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) (Test Automation Framework) und Cucumber.
`zmsautomation` baut auf [it-at-m/agile-test-automation-framework](https://it-at-m.github.io/agile-test-automation-framework/) auf. Es nutzt ATAF **ohne Jira** (Features in Git) und **lokales Keycloak** für SSO — siehe [Lokales Keycloak-Setup](#lokales-keycloak-setup) und das ATAF-Handbuch [Standalone-Nutzung (ohne Jira, lokales Keycloak)](https://it-at-m.github.io/agile-test-automation-framework/de/usage/standalone-without-jira.html).

## Voraussetzungen

- Java 21
- Maven 3.9+
- MySQL-/MariaDB-Datenbank (für lokale Tests)
- Zugriff auf Maven Central ([ATAF](https://it-at-m.github.io/agile-test-automation-framework/)-Artefakte werden unter `de.muenchen.ataf:core|rest|web` veröffentlicht)

## Projektstruktur

- `src/test/java/zms/api/` – ursprüngliche REST-assured + JUnit-Tests (Standalone-Profil)
- `src/test/java/zms/ataf/`
  - `zms/ataf/rest/steps/` – REST-Step-Definitionen (REST Assured)
  - `zms/ataf/ui/steps/` – UI-Step-Definitionen (Selenium/[ATAF](https://it-at-m.github.io/agile-test-automation-framework/) web)
  - `zms/ataf/ui/pages/**` – Page-Objects für Admin, Statistik, Buergeransicht, Mailinator
- `src/test/resources/features/` – Cucumber-Feature-Dateien
  - `rest/zmsapi/` - ZMS REST API features (legacy folder/tag name; targets `zmsbackend` at `/terminvereinbarung/api/2`)
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
# alle [ATAF](https://it-at-m.github.io/agile-test-automation-framework/)-Tests ausführen (API + UI)
./zmsautomation/zmsautomation-test -Pataf-api -Pataf-ui

# Tests mit bestimmten Tags ausführen (mit @ignore markierte Szenarien sind ausgeschlossen, sofern @ignore nicht im Ausdruck enthalten ist)
./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@smoke"
# inklusive ignorierter Szenarien, z. B.:
# ./zmsautomation/zmsautomation-test -Dcucumber.filter.tags="@ignore and @web"

# spezifisches API-Feature ausführen (Pfad relativ zum zmsautomation-Modul)
./zmsautomation/zmsautomation-test -Pataf-api -Dcucumber.features="src/test/resources/features/rest/zmsapi/status.feature"

# nur API-Tests (kein Selenium)
./zmsautomation/zmsautomation-test -Pataf-api

# nur UI-Tests (Selenium/[ATAF](https://it-at-m.github.io/agile-test-automation-framework/) web)
./zmsautomation/zmsautomation-test -Pataf-ui
```

Das Skript:

1. Sichert die Datenbank
2. Leert Caches
3. Setzt die Datenbank zurück (Tabellen verwerfen)
4. Importiert die Basis-Datenbank (`.resources/zms.sql`)
5. Führt Flyway-Migrationen aus (Maven-Plugin)
6. Führt PHP-Migrationen aus (`zmsbackend` migrate)
7. Führt den Stunden-Cron aus (mit Wiederholungen)
8. Führt den Minuten-Cron und die Slot-Berechnung aus (`calculateSlots`)
9. Führt HTTP-Health-Checks aus (zmsbackend, Citizen-API, CitizenView, optionales refarch-gateway)
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

### [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) als Standard

Das Maven-Profil `ataf` ist in diesem Modul standardmäßig aktiv, daher binden `mvn test` und `mvn test-compile` bereits die [ATAF](https://it-at-m.github.io/agile-test-automation-framework/)-/Cucumber-/TestNG-/Selenium-Abhängigkeiten ein.

### [ATAF](https://it-at-m.github.io/agile-test-automation-framework/)-Profile (lokale Entwicklung)

- Alle [ATAF](https://it-at-m.github.io/agile-test-automation-framework/)-Tests ausführen (API + UI):

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

- Nur UI-Tests (Selenium/[ATAF](https://it-at-m.github.io/agile-test-automation-framework/) web, kein REST Assured):

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

## Lokales Keycloak-Setup {#lokales-keycloak-setup}

`zmsautomation` nutzt [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) **ohne Jira**: alle Cucumber-Features liegen unter `src/test/resources/features/`, UI-Tests authentifizieren sich gegen ein **lokales Keycloak** — dasselbe [keycloakmigration](https://github.com/klg71/keycloakmigration)-Muster wie im [RefArch-Stack](https://github.com/it-at-m/refarch-templates/tree/main/stack/keycloak/migration). Für lokale Läufe brauchst du kein Corporate- oder Behörden-SSO (`ssodev`).

Siehe auch das ATAF-Handbuch: [Standalone-Nutzung (ohne Jira, lokales Keycloak)](https://it-at-m.github.io/agile-test-automation-framework/de/usage/standalone-without-jira.html).

### Docker-Compose-Dienste

Keycloak und der Migrations-Sidecar sind definiert in:

- [`.ddev/docker-compose.keycloak.yaml`](https://github.com/it-at-m/eappointment/blob/main/.ddev/docker-compose.keycloak.yaml) (DDEV)
- [`.devcontainer/docker-compose.yaml`](https://github.com/it-at-m/eappointment/blob/main/.devcontainer/docker-compose.yaml) (devcontainer / Podman)

Beide Stacks starten:

| Dienst          | Aufgabe                                                                                     |
| --------------- | ------------------------------------------------------------------------------------------- |
| `keycloak`      | `quay.io/keycloak/keycloak:26.6.3`, `start-dev`, `KC_HTTP_RELATIVE_PATH=/auth`, Port `8080` |
| `init-keycloak` | `klg71/keycloakmigration:0.2.129`, wendet Migrationen an, sobald Keycloak bereit ist        |

`init-keycloak` bindet [`.resources/keycloak/migration/`](https://github.com/it-at-m/eappointment/tree/main/.resources/keycloak/migration) ein und liest `KEYCLOAK_CHANGELOG=/migration/keycloak-changelog.yml`.

### Migrations-Changelog

Der Changelog wendet die Realm-Konfiguration in dieser Reihenfolge an:

```yaml
includes:
  - path: 01_init-realm.yml # Realm zms
  - path: 02_add-clients.yml # OIDC-Client zms, Redirect-URIs für Admin/Statistik
  - path: 03_add-roles.yml
  - path: 04_add-users.yml
  - path: 05_assign-roles.yml
  - path: 06_zms-audience.yml
  - path: 07_add-system-users.yml # Testbenutzer ataf (Passwort vorschau)
  - path: 08_add-role-test-users.yml
```

Das entspricht dem RefArch-Muster ([`stack/docker-compose.yml`](https://github.com/it-at-m/refarch-templates/blob/4735e9f425a29e9cd38eafc6cd34b5da705f0574/stack/docker-compose.yml#L52)), nutzt aber einen ZMS-spezifischen Realm, Clients und Benutzer.

### Hostname `keycloak`

Anwendungen und Browser-Redirects erwarten den Hostnamen `keycloak`, nicht `localhost`. Trage `127.0.0.1 keycloak` in die Hosts-Datei ein und starte den Stack neu — siehe [Lokale Keycloak-Einrichtung](../setup-and-development/local-keycloak-setup.md).

### ATAF-Test-Properties

`zmsautomation/src/test/resources/testautomation.properties` verknüpft ATAF mit dem migrierten Benutzer und umgeht den Corporate-Proxy für Docker-Hostnamen:

```properties
testautomation.userName=ataf
testautomation.userPassword=vorschau
testautomation.noProxy=keycloak,citizenview,refarch-gateway,localhost,127.0.0.1
```

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

[ATAF](https://it-at-m.github.io/agile-test-automation-framework/)-Tests führen vor der Testausführung automatisch Flyway-Migrationen aus. Die Migrationen liegen unter `src/main/resources/db/migration/`.

## Beispiele für Test-Tags

- API-Tags:
  - `@rest`
  - `@zmsapi` (Legacy-Tag; REST-API wird von `zmsbackend` bereitgestellt)
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

- `rest/zmsapi/status.feature` – Tests des Status-Endpoints (gegen `zmsbackend`)
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

- `zmsautomation` nutzt [ATAF](https://it-at-m.github.io/agile-test-automation-framework/) + Cucumber; CI-/Workflow-Umgebungen können separat festgepinnt werden.

## Bekannte Einschränkungen

### Buchungstests an gesetzlichen Feiertagen

Die Slot-Berechnung erzeugt an gesetzlichen Feiertagen absichtlich keine Termine (Datensätze in der Tabelle `feiertage`, befüllt durch Migration V11). Die Testdaten für Öffnungszeiten in zmsautomation (relativ zum aktuellen Datum, z. B. in den Migrationen V10 und V19) können in einen Feiertag fallen. In diesem Fall gibt es am „ersten verfügbaren Tag“ ggf. keine buchbaren Slots und buchungsbezogene Szenarien (Citizen API und CitizenView Flows) können fehlschlagen.

Das ist erwartetes Verhalten. Maßnahme: Pipeline am nächsten arbeitsfreien Nicht‑Feiertag erneut ausführen (oder lokal an einem Nicht‑Feiertag starten).

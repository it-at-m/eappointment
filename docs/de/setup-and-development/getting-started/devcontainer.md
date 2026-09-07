# Devcontainer und Podman

Die lokale Entwicklung nutzt **Podman** mit **Dev Containers**. Starte den Stack mit:

```bash
devcontainer up --workspace-folder .
```

Das erzeugt und startet die lokale Container-Umgebung für Entwicklung und Tests. Der Befehl erkennt außerdem automatisch deine Host-Architektur (`amd64` vs. `arm64`) und zieht für `zms-web` das passende lokale PHP-Basis-Image-Tag.

Wie du Podman auf dem Host vollständig bereinigst und den Stack neu erzeugst, beschreibt [Schnelles Zurücksetzen der lokalen Umgebung](/de/setup-and-development/getting-started/quick-reset-local-environment).

Auf **macOS** siehe [Podman und Dev Containers unter macOS](/de/setup-and-development/getting-started/macos-local-configuration/podman-and-dev-containers).

## Container und lokale Endpunkte

Folgende lokale Container werden bei `devcontainer up --workspace-folder .` automatisch erzeugt:

- `zms-web` (vorgebautes lokales PHP-Basis-Image), App-Endpunkt: `http://localhost:8090`
- `zms-refarch-gateway` (lokales RefArch-API-Gateway), Endpunkt: `http://localhost:8084`
- `zms-keycloak` (gleiche Keycloak-Image-Familie wie die RefArch-Einrichtung), Endpunkt: `http://localhost:8080/auth`
- `zms-db` (MariaDB), DB-Port: `3306`
- `zms-phpmyadmin`, Endpunkt: `http://localhost:8036`
- `zms-citizenview`, Vite-Hot-Reload-Endpunkt: `http://localhost:8082`
- `zms-mail-viewer`, lokaler Mail-Queue-Viewer: `http://localhost:8025` (Login `superuser` / `vorschau`)

## Lokaler Mail-Queue-Viewer

ZMS legt ausgehende Mails in der API-Queue (`GET /api/2/mails/`) ab, bevor `zmsmessaging` sie versendet. Lokal ist SMTP meist deaktiviert — Mails bleiben in der Queue.

Der Sidecar `zms-mail-viewer` pollt diese API und zeigt Betreff, Empfänger, HTML-Vorschau und Klartext. Er läuft nur in der lokalen Dev-Container-Umgebung.

- URL: `http://localhost:8025`
- Login: `superuser` / `vorschau` (Browser-Basic-Auth; konfigurierbar über `ZMS_MAIL_VIEWER_USER` / `ZMS_MAIL_VIEWER_PASSWORD`)
- API-Auth: lokale Superuser-Zugangsdaten über `ZMS_MAIL_VIEWER_API_USER` / `ZMS_MAIL_VIEWER_API_PASSWORD` (Standard `superuser` / `vorschau`)

## Automatische Einrichtung beim Start

Beim lokalen Start bereitet die Umgebung außerdem den Hauptentwicklungs-Flow vor:

- führt `composer install`, `npm install` und `npm build` in `zms-web` aus
- richtet `zmscitizenview` im lokalen Container `zms-citizenview` mit `npm install` und `npm run dev` ein und startet Hot Reload auf `localhost:8082`
- installiert in `zms-web` Werkzeuge zur Browser-Automatisierung für lokale `zmsautomation`-Läufe (Firefox/Xvfb sowie WebDriver-Unterstützung für Chrome/Chromium, Edge und Firefox via `chromedriver`, `msedgedriver` und `geckodriver`)

Damit umfasst `devcontainer up --workspace-folder .` bereits den Install-/Build-/Bootstrap-Flow inklusive `./cli db full-setup`.

Modul-Abhängigkeits-/Build-Befehle kannst du jederzeit erneut ausführen:

```bash
podman exec -it zms-web bash -lc "./cli modules loop composer install"
podman exec -it zms-web bash -lc "./cli modules loop npm install"
podman exec -it zms-web bash -lc "./cli modules loop npm build"
```

## Datenbank-Initialisierung (`./cli db full-setup`)

Die lokale Einrichtung führt `./cli db full-setup` aus und:

- importiert die Basis-DB aus `.resources/zms.sql`
- importiert DLDB-Standorte/-Anliegen über den Stunden-Cron-Flow
- importiert produktionsnahe Testdaten aus `zmsautomation`-Flyway-Migrationen
- führt Datenbankmigrationen aus
- führt den Minuten-Cron aus, um öffnungszeitenbezogene Testdaten zu erzeugen

Du kannst die vollständige Einrichtung jederzeit erneut ausführen:

```bash
podman exec -it zms-web bash -lc "./cli db full-setup"
```

Hinweise zur Keycloak-Hostzuordnung und zu Linux-Podman siehe [Lokale Keycloak-Einrichtung](../local-keycloak-setup.md).

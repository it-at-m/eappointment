# E-Appointment PHP-Basis-Images (`zmsbase`)

Infrastrukturgrundlage: Das Verzeichnis [`zmsbase`](https://github.com/it-at-m/eappointment/tree/main/zmsbase) in diesem Monorepo stellt standardisierte, vorgebaute PHP-Laufzeit-Images für eappointment-[Modul-Builds](https://github.com/it-at-m/eappointment/blob/main/.github/workflows/php-build-images.yaml) über das [Containerfile](https://github.com/it-at-m/eappointment/blob/main/.resources/Containerfile) bereit.

- Quellcode: [`zmsbase/`](https://github.com/it-at-m/eappointment/tree/main/zmsbase) in [it-at-m/eappointment](https://github.com/it-at-m/eappointment)
- Früheres Standalone-Repository: [it-at-m/eappointment-php-base](https://github.com/it-at-m/eappointment-php-base) (ersetzt durch `zmsbase`)
- Ursprüngliches Berliner Repository: [gitlab.com/eappointment/php-base](https://gitlab.com/eappointment/php-base)

## Image-Varianten und Verwendung

Basierend auf [`.github/workflows/zmsbase-build-images.yaml`](https://github.com/it-at-m/eappointment/blob/main/.github/workflows/zmsbase-build-images.yaml) veröffentlicht das Projekt drei Gruppen von Images:

- `8.4-base` und `8.4-dev` aus `zmsbase/php84/Dockerfile`
- `8.3-base` und `8.3-dev` aus `zmsbase/php83/Dockerfile`
- `8.3-local-amd64` und `8.3-local-arm64` aus `zmsbase/php83-local/Dockerfile`

Die Rollenaufteilung:

- Lokale Images (`8.3-local-amd64`, `8.3-local-arm64`) sind für lokale Entwicklung und `zmsautomation` gedacht.
- Nicht-lokale Images (`8.3-*`, `8.4-*`) sind für produktionsnahe/laufzeitorientierte Umgebungen gedacht.

Diese duale lokale Architektur unterstützt die Entwicklung auf macOS Apple Silicon und anderen Nicht-amd64-Umgebungen und bietet zugleich linux/amd64-Kompatibilität.

## Lokale Architekturunterstützung

Der Job `php_v8_3_local` baut Single-Architecture-Tags in einer Matrix:

- `linux/amd64` auf `ubuntu-latest` → `8.3-local-amd64`
- `linux/arm64` auf `ubuntu-24.04-arm` → `8.3-local-arm64`

Devcontainer und DDEV setzen `ZMS_PHP_BASE_TAG` über [`.devcontainer/scripts/sync-php-base-tag.sh`](https://github.com/it-at-m/eappointment/blob/main/.devcontainer/scripts/sync-php-base-tag.sh).

## Verhalten des Build- und Veröffentlichungs-Workflows

Workflow: [🐳 Build ZMS base images](https://github.com/it-at-m/eappointment/blob/main/.github/workflows/zmsbase-build-images.yaml) (`zmsbase-build-images.yaml`).

Er läuft bei:

- Pushes, die `zmsbase/**` oder die Workflow-Datei ändern (alle Branches und Tags)
- monatlichem Zeitplan (`0 0 1 * *`)
- manuellem `workflow_dispatch`

Jeder Image-Job meldet sich bei GHCR an, baut die Image-Targets, validiert den PHP-Start (`php-fpm -t` oder `php -v`) und pusht die resultierenden Tags nach:

- `ghcr.io/it-at-m/eappointment/zmsbase`

## Modul-Abhängigkeitskontext

```mermaid
%%{init: {"flowchart": {"defaultRenderer":"elk"}}}%%
graph TD
    subgraph InfrastructureFoundation["Infrastructure Foundation"]
        PHPBASE["zmsbase<br>PHP Docker Base Images<br>Runtime Environment"]
    end

    zmsapi --> zmsslim
    zmsapi --> zmsclient
    zmsapi --> zmsdldb
    zmsapi --> zmsdb
    zmsapi --> zmsentities

    zmsadmin --> mellon
    zmsadmin --> zmsclient
    zmsadmin --> zmsslim
    zmsadmin --> zmsentities

    zmscalldisplay --> mellon
    zmscalldisplay --> zmsclient
    zmscalldisplay --> zmsentities
    zmscalldisplay --> zmsslim

    zmsstatistic --> mellon
    zmsstatistic --> zmsentities
    zmsstatistic --> zmsslim
    zmsstatistic --> zmsclient

    zmsmessaging --> mellon
    zmsmessaging --> zmsclient
    zmsmessaging --> zmsentities
    zmsmessaging --> zmsslim

    zmsdb --> zmsentities
    zmsdb --> zmsdldb
    zmsdb --> mellon

    zmsclient --> zmsentities
    zmsclient --> zmsslim
    zmsclient --> mellon

    zmsentities --> mellon
    zmsslim --> mellon

    zmscitizenapi --> mellon
    zmscitizenapi --> zmsslim
    zmscitizenapi --> zmsclient
    zmscitizenapi --> zmsentities

    zmscitizenapi -.-> zmsapi
    refarch_gateway -.-> zmscitizenapi
    zmscitizenview -.-> refarch_gateway

    PHPBASE -->|stellt Laufzeit bereit für| zmsapi
    PHPBASE -->|stellt Laufzeit bereit für| zmsadmin
    PHPBASE -->|stellt Laufzeit bereit für| zmscalldisplay
    PHPBASE -->|stellt Laufzeit bereit für| zmsstatistic
    PHPBASE -->|stellt Laufzeit bereit für| zmsmessaging
    PHPBASE -->|stellt Laufzeit bereit für| zmsdb
    PHPBASE -->|stellt Laufzeit bereit für| zmsclient
    PHPBASE -->|stellt Laufzeit bereit für| zmsentities
    PHPBASE -->|stellt Laufzeit bereit für| zmsslim
    PHPBASE -->|stellt Laufzeit bereit für| zmsdldb
    PHPBASE -->|stellt Laufzeit bereit für| mellon
    PHPBASE -->|stellt Laufzeit bereit für| zmscitizenapi

    subgraph refarch["refarch"]
        style refarch stroke-dasharray:5
        refarch_gateway
        zmscitizenview
    end

    subgraph zms_modules["ZMS PHP Modules"]
        style zms_modules stroke-dasharray:5 5 1 5
        zmsapi
        zmsadmin
        zmscalldisplay
        zmsstatistic
        zmsmessaging
        zmsdb
        zmsclient
        zmsentities
        zmsslim
        zmsdldb
        mellon
        zmscitizenapi
    end

    classDef foundation fill:#e3f2fd,stroke:#0277bd,stroke-width:3px
    classDef citizenapi fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef gateway fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
    classDef citizenview fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px

    class PHPBASE foundation
    class zmscitizenapi citizenapi
    class refarch_gateway gateway
    class zmscitizenview citizenview
```

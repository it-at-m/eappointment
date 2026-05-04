# E-Appointment PHP Base Images

Infrastructure Foundation: `eappointment-php-base` provides standardized pre-built PHP runtime environments for eappointment [build](https://github.com/it-at-m/eappointment/blob/main/.github/workflows/php-build-images.yaml#L43) via the [Containerfile](https://github.com/it-at-m/eappointment/blob/main/.resources/Containerfile).

- External Munich repository: [it-at-m/eappointment-php-base](https://github.com/it-at-m/eappointment-php-base)
- Original Berlin repository: [gitlab.com/eappointment/php-base](https://gitlab.com/eappointment/php-base)

## Image variants and usage

Based on `eappointment-php-base/.github/workflows/build-images.yaml`, the project publishes three groups of images:

- `8.4-base` and `8.4-dev` from `php84/Dockerfile`
- `8.3-base` and `8.3-dev` from `php83/Dockerfile`
- `8.3-local-amd64` and `8.3-local-arm64` from `php83-local/Dockerfile`

The role split is:

- Local images (`8.3-local-amd64`, `8.3-local-arm64`) are intended for local development and `zmsautomation`.
- Non-local images (`8.3-*`, `8.4-*`) are intended for production/runtime-aligned environments.

This dual-architecture local setup supports development on macOS Apple Silicon and other non-amd64 environments while still providing linux/amd64 compatibility.

## Local architecture support

The `php_v8_3_local` job builds single-architecture tags in a matrix:

- `linux/amd64` on `ubuntu-latest` -> `8.3-local-amd64`
- `linux/arm64` on `ubuntu-24.04-arm` -> `8.3-local-arm64`

This ensures local Linux images are available for both major architectures used in developer machines and CI execution contexts.

## Build and publish workflow behavior

The workflow runs on:

- pushes to all branches (`'*'`)
- pushes of all tags (`'*'`)
- monthly schedule (`0 0 1 * *`)

Each image job logs in to GHCR, builds image targets, validates PHP startup (`php-fpm -t` or `php -v`), and pushes resulting tags to:

- `ghcr.io/it-at-m/eappointment-php-base`

## Module dependency context

```mermaid
%%{init: {"flowchart": {"defaultRenderer":"elk"}}}%%
graph TD
    subgraph InfrastructureFoundation["Infrastructure Foundation"]
        PHPBASE["eappointment-php-base<br>PHP Docker Base Images<br>Runtime Environment"]
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

    PHPBASE -->|provides runtime for| zmsapi
    PHPBASE -->|provides runtime for| zmsadmin
    PHPBASE -->|provides runtime for| zmscalldisplay
    PHPBASE -->|provides runtime for| zmsstatistic
    PHPBASE -->|provides runtime for| zmsmessaging
    PHPBASE -->|provides runtime for| zmsdb
    PHPBASE -->|provides runtime for| zmsclient
    PHPBASE -->|provides runtime for| zmsentities
    PHPBASE -->|provides runtime for| zmsslim
    PHPBASE -->|provides runtime for| zmsdldb
    PHPBASE -->|provides runtime for| mellon
    PHPBASE -->|provides runtime for| zmscitizenapi

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

## Note

This repository may be moved into the eappointment monorepo in the near future.

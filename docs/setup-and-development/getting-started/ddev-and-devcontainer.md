# DDEV and Devcontainer

## Start local stack

```bash
# DDEV
ddev start
```
```bash
# Devcontainer (Podman)
devcontainer up --workspace-folder .
```

`ddev start` or `devcontainer up --workspace-folder .` creates and starts the local container setup used for development and testing.

Both commands also auto-detect your host architecture (`amd64` vs `arm64`) and pull the matching local PHP base image tag for `zms-web`.

To wipe Podman or Docker completely on the host and recreate the stack, see [Quick reset of the local environment](/setup-and-development/getting-started/quick-reset-local-environment).

On **macOS**, see [Podman and Dev Containers on macOS](/setup-and-development/getting-started/macos-local-configuration/podman-and-dev-containers) and [Local HTTPS SSL for DDEV (macOS)](/setup-and-development/getting-started/macos-local-configuration/local-https-ddev).

## Containers and local endpoints

The following local containers are automatically created when running `ddev start` or `devcontainer up --workspace-folder .`:

- `zms-web` (pre-built local PHP base image), app endpoint: `http://localhost:8090`
- `zms-refarch-gateway` (RefArch API gateway image), endpoint: `http://localhost:8084`
- `zms-keycloak` (same Keycloak image family as RefArch setup), endpoint: `http://localhost:8080/auth`
- `zms-db` (MariaDB), DB port: `3306`
- `zms-phpmyadmin`, endpoint: `http://localhost:8036`
- `zms-citizenview`, Vite hot reload endpoint: `http://localhost:8082`

## Automatic setup on startup

During local startup, the environment also prepares the main development flow:

- runs `composer install`, `npm install`, and `npm build` in `zms-web`
- sets up and launches `zmscitizenview` in a local container `zms-citizenview` with `npm install` and `npm run dev` dependencies and starts hot reload on `localhost:8082`
- installs browser automation tooling in `zms-web` for local `zmsautomation` runs (Firefox/Xvfb plus WebDriver support for Chrome/Chromium, Edge, and Firefox via `chromedriver`, `msedgedriver`, and `geckodriver`)

This means `ddev start` and `devcontainer up --workspace-folder .` already include the install/build/bootstrap flow, including `./cli db full-setup`.

You can still rerun module dependency/build commands at any time:

```bash
# DDEV
ddev exec ./cli modules loop composer install
ddev exec ./cli modules loop npm install
ddev exec ./cli modules loop npm build
```
```bash
# Podman
podman exec -it zms-web bash -lc "./cli modules loop composer install"
podman exec -it zms-web bash -lc "./cli modules loop npm install"
podman exec -it zms-web bash -lc "./cli modules loop npm build"
```

## Database initialization (`./cli db full-setup`)

The local setup runs `./cli db full-setup`, which:

- imports the base DB from `.resources/zms.sql`
- imports DLDB offices/services via hourly cronjob flow
- imports production-like test data from `zmsautomation` Flyway migrations
- runs database migrations
- runs the minutly cronjob to generate opening-hours-related test data

You can rerun full setup at any time:

```bash
# DDEV
ddev exec ./cli db full-setup
```
```bash
# Podman
podman exec -it zms-web bash -lc "./cli db full-setup"
```

For Keycloak host mapping and Linux Podman notes, see [Local Keycloak Setup](../local-keycloak-setup.md).

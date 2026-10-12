# Xdebug (local zms-web)

Local `zms-web` mounts `.devcontainer/php/xdebug.ini` into PHP. On macOS Podman that default (`debug,develop` + `start_with_request=yes`) makes Apache and PHPUnit very slow.

`.devcontainer/php/xdebug.ini` is **gitignored**. Copy the example once, then toggle locally without committing.

## First-time setup

```bash
cp .devcontainer/php/xdebug.ini.example .devcontainer/php/xdebug.ini
```

Dev Containers `initializeCommand` also creates the file from the example when it is missing.

## Disable (recommended on macOS)

In `.devcontainer/php/xdebug.ini` set:

```ini
xdebug.mode=off
```

Reload Apache (no container recreate):

```bash
podman exec zms-web apache2ctl graceful
```

The `AH00558` ServerName message is harmless.

## Enable for debugging

Restore:

```ini
xdebug.mode=debug,develop
```

Then `apache2ctl graceful` again. `XDEBUG_CLIENT_HOST` comes from `.devcontainer/.env` (see `.env.template`).

## PHPUnit only (leave Apache as-is)

```bash
podman exec -it zms-web bash -lc 'export XDEBUG_MODE=off; cd zmsadmin && ./vendor/bin/phpunit --no-coverage'
```

`export XDEBUG_MODE=off;` must run before `cd`. Do not use `XDEBUG_MODE=off cd … && ./vendor/bin/phpunit` — that applies the variable only to `cd`.

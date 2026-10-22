# Xdebug (lokales zms-web)

Lokales `zms-web` mountet `.devcontainer/php/xdebug.ini` in PHP. Unter macOS/Podman machen die Defaults (`debug,develop` und `start_with_request=yes`) Apache und PHPUnit sehr langsam.

`.devcontainer/php/xdebug.ini` ist **gitignored**. Einmal vom Example kopieren, danach lokal umschalten — ohne Commit.

## Erstes Setup

```bash
cp .devcontainer/php/xdebug.ini.example .devcontainer/php/xdebug.ini
```

Der Dev-Containers-`initializeCommand` legt die Datei ebenfalls aus dem Example an, wenn sie fehlt.

## Ausschalten (empfohlen unter macOS)

In `.devcontainer/php/xdebug.ini`:

```ini
xdebug.mode=off
```

Apache neu laden (ohne Container-Recreate):

```bash
podman exec zms-web apache2ctl graceful
```

Die Meldung `AH00558` (ServerName) ist harmlos.

## Fürs Debuggen einschalten

Wieder setzen:

```ini
xdebug.mode=debug,develop
```

Danach erneut `apache2ctl graceful`. `XDEBUG_CLIENT_HOST` kommt aus `.devcontainer/.env` (siehe `.env.template`).

## Nur PHPUnit (Apache unverändert)

```bash
podman exec -it zms-web bash -lc 'export XDEBUG_MODE=off; cd zmsadmin && ./vendor/bin/phpunit --no-coverage'
```

`export XDEBUG_MODE=off;` muss vor `cd` stehen. Nicht `XDEBUG_MODE=off cd … && ./vendor/bin/phpunit` verwenden — die Variable gilt dann nur für `cd`.

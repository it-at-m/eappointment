# Devcontainer and Podman Commands

## Getting Started

- `devcontainer up --workspace-folder .`
- `podman exec -it zms-web bash -lc "./cli modules loop composer install"`
- `podman exec -it zms-web bash -lc "./cli modules loop npm install"`
- `podman exec -it zms-web bash -lc "./cli modules loop npm build"`

## Import Database

- `podman exec -i zms-db mysql -u root -proot db < .resources/zms.sql`
- `podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate --update"`

## Stop Containers

- `podman stop $(podman ps -aq)`

> [!NOTE]
> The [dev container CLI](https://github.com/devcontainers/cli) is currently in active development. The `devcontainer stop` & `devcontainer down` commands haven't been implemented yet, that's why we use `podman stop $(podman ps -aq)` for the time being.

## Code Quality Checks

### PHPCS

Run all checks at once:

```bash
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcs --standard=psr12 src/'" && podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src'"
```

Run checks for one specific module:

```bash
podman exec -it zms-web bash

# Go to the desired module directory:
cd zmsadmin

# Run PHPCS
vendor/bin/phpcs --standard=psr12 src/

# Automatically fix formatting issues by running the following:
vendor/bin/phpcbf --standard=psr12 src/
# or run:
phpcs --standard=psr12 --fix src/

# Run PHPMD
vendor/bin/phpmd src/ text ../phpmd.rules.xml
```

## Unit Testing

- `podman exec -it zms-web bash`
- `cd {zmsadmin, zmscalldisplay, zmsdldb, zmsentities, zmsmessaging, zmsslim, zmsstatistic, zmsticketprinter}`
- `./vendor/bin/phpunit`

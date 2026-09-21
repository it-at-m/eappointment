# Code Quality Checks

This page summarizes formatting and code-quality checks used across the eappointment modules.
Because the repository contains PHP, JavaScript/TypeScript, and Java components, each module area uses its own tooling and commands.
Git hooks ([Git hooks (Husky)](./git-hooks.md)) run many of these checks automatically before each commit when Husky is set up.

## PHP Formatting

We use PHPCS (following PSR-12 standards) and PHPMD to maintain code quality and detect possible issues early. These checks run automatically in our GitHub Actions pipeline but can also be executed locally.

```bash
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcs --standard=psr12 src/'" && \
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src'"
```

## Frontend ESLint (zmsadmin, zmsstatistic, zmscalldisplay, zmsticketprinter)

These four modules use ESLint 10 (`npm run lint` → `eslint js/`). GitHub Actions runs the same check. Run it locally inside the `zms-web` container.

All four (`./cli modules loop` already limits `npm` to these modules):

```bash
podman exec -it zms-web bash -lc "./cli modules loop npm run lint"
podman exec -it zms-web bash -lc "./cli modules loop npm run fix"
```

One module:

```bash
podman exec -it zms-web bash -lc "cd zmsadmin && npm run lint"
podman exec -it zms-web bash -lc "cd zmsstatistic && npm run lint"
podman exec -it zms-web bash -lc "cd zmscalldisplay && npm run lint"
podman exec -it zms-web bash -lc "cd zmsticketprinter && npm run lint"
```

## zmscitizenview JS Formatting

We use `prettier-codeformat` for checking and formatting code style in zmscitizenview. You can use format function to fix
code style (lint) problems:

1. Go to `zmscitizenview`

```bash
cd zmscitizenview
```

2. Run:

```bash
npm run format
```

## VitePress docs formatting

The `docs/` site uses Prettier (`@muenchen/prettier-codeformat`). The pre-commit hook runs `npm run format:check` when Husky is enabled.

```bash
cd docs
npm install   # once per machine
npm run format:check
npm run format   # apply fixes
```

## zmsautomation Maven Formatting

`zmsautomation` uses the Maven Spotless plugin for Java formatting.

Go to the module:

```bash
cd zmsautomation
```

Check formatting:

```bash
mvn spotless:check
```

Apply formatting fixes:

```bash
mvn spotless:apply
```

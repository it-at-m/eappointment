# Code Quality Checks

This page summarizes formatting and code-quality checks used across the eappointment modules.
Because the repository contains PHP, JavaScript/TypeScript, and Java components, each module area uses its own tooling and commands.
Git hooks ([Git hooks (Husky)](./git-hooks.md)) run many of these checks automatically before each commit when Husky is set up.

## PHP Formatting

We use PHPCS (following PSR-12 standards) and PHPMD to maintain code quality and detect possible issues early. These checks run automatically in our GitHub Actions pipeline but can also be executed locally.

### Using DDEV

```bash
ddev exec "./cli modules loop 'vendor/bin/phpcs --standard=psr12 src/'" && \
ddev exec "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src'"
```

### Using Podman

```bash
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcs --standard=psr12 src/'" && \
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src'"
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

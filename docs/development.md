# Development

## Code Quality

PHP quality checks are based on PHPCS (PSR-12) and PHPMD.

Example (inside a module):

```bash
vendor/bin/phpcs --standard=psr12 src/
vendor/bin/phpcbf --standard=psr12 src/
vendor/bin/phpmd src/ text ../phpmd.rules.xml
```

For `zmscitizenview`, formatting is done via:

```bash
cd zmscitizenview
npm run format
```

## Dependency Upgrade Check

Use the CLI helper to evaluate upgrades:

```bash
ddev exec ./cli modules check-upgrade 8.3
```

## Branch Naming Convention

The repository convention includes branch types such as:

- `feature-*`
- `bugfix-*`
- `hotfix-*`
- `cleanup-*`
- `docs-*`
- `chore-*`

Use lowercase and hyphenated descriptions.

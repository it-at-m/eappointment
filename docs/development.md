# Development

## Code Quality

PHP quality checks use **PHPCS** and **PHPMD**. GitHub Actions runs PHPCS with **PSR-12** (`psr12`); see [`.github/workflows/php-code-quality.yaml`](../.github/workflows/php-code-quality.yaml).

Example (inside a module, aligned with CI):

```bash
vendor/bin/phpcs --standard=psr12 src/
vendor/bin/phpcbf --standard=psr12 src/
vendor/bin/phpmd src/ text ../phpmd.rules.xml
```

**Note:** `mellon`, `zmsslim`, and `zmsdldb` ship a `phpcs.xml` that references **PSR2** (`<rule ref="PSR2" />`). If you run `vendor/bin/phpcs` **without** `--standard` in those modules, that project file may apply PSR2 instead. Use `--standard=psr12` as above so local results match CI.

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

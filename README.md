# E-Appointment

## Getting Started
- `ddev start`
- `ddev exec ./cli modules loop composer install`

## Import Database
- `ddev import-db --file=.resources/zms.sql`
- `ddev exec zmsapi/vendor/bin/migrate --update`

## Dependency Check for PHP Upgrades
Pass the PHP version that you would want to upgrade to and recieve information about dependency changes patch, minor, or major for each module.
e.g.
- `ddev exec ./cli modules check-upgrade 8.1`
- `ddev exec ./cli modules check-upgrade 8.2`
- `ddev exec ./cli modules check-upgrade 8.3`

# E-Appointment

## Getting Started
- `ddev start`
- `ddev exec ./cli modules loop composer install`

## Import Database
- `ddev import-db --file=.resources/zms.sql`
- `ddev exec zmsapi/vendor/bin/migrate --update`

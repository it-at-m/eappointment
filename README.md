# E-Appointment

## Getting Started
- `git submodule update --init`
- `ddev start`
- `ddev exec ./cli repos reference-libraries`

## Import Database
- `ddev import-db --file=.resources/zms.sql`
- `ddev exec zmsapi/vendor/bin/migrate --update`

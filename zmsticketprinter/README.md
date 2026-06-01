# ZMS Ticketprinter

[![CI](https://github.com/it-at-m/eappointment/actions/workflows/combined-workflow-with-docs.yaml/badge.svg?branch=main)](https://github.com/it-at-m/eappointment/actions/workflows/combined-workflow-with-docs.yaml)
[![coverage report](https://img.shields.io/badge/coverage-report-blue)](https://it-at-m.github.io/eappointment/coverage/coverage-zmsticketprinter/html/)

Frontend for ticket printers in the ZMS queue system.

## Requirements

* PHP 8.3+

## Installation

The variable `$WEBROOT` represents the parent path to install the application.

```bash
cd $WEBROOT
git clone https://github.com/it-at-m/eappointment.git
cd eappointment/zmsticketprinter
make live
cp config.example.php config.php
```

Edit the `config.php` and add/change settings for accessing the API.

To enable the application, point the webserver to the `public/` path in the installation.

## Development

For development, additional modules from the monorepo are required.

```bash
make dev
make build
```

## Testing

To test the application run:

    bin/test

For a detailed project description, see https://github.com/it-at-m/eappointment

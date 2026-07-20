# ZMS Messaging

[![CI](https://github.com/it-at-m/eappointment/actions/workflows/combined-workflow-with-docs.yaml/badge.svg?branch=main)](https://github.com/it-at-m/eappointment/actions/workflows/combined-workflow-with-docs.yaml)
[![coverage report](https://img.shields.io/badge/coverage-report-blue)](https://it-at-m.github.io/eappointment/coverage/coverage-zmsmessaging/html/)

# ZMS HTTP messaging

Use this library to messaging email and notifications.

## Requirements

* PHP 7.3+

## Installation

The variable `$WEBROOT` represents the parent path to install the application.

```bash
    cd $WEBROOT
    git clone https://github.com/it-at-m/eappointment.git
    cd eappointment/zmsmessaging
    make live
    cp config.example.php config.php
```

## Development

For development, additional modules are required. Commits from a live environment require to ignore the pre-commit hooks.
For local development do

```bash
    ...
    make dev
    ...
```

## Configuration

Edit the `config.php` and add/change settings for accessing the API.

## Usage

To start sending emails from a database queue, the following scripts can be integrated into a cron job that processes the jobs at a desired interval.

To start a dispatch, the parameter --send should be set. If you also want an output of the sent jobs, the parameter --verbose can be added. A DryRun is obtained by using only the --verbose parameter without --send.

```
php -d mail.add_x_header=0 $BIN/mail_queue.php --send "$@"
php -d mail.add_x_header=0 $BIN/notification_queue.php --send "$@"
```

## Testing

To test application run the following command:

    bin/test

For a detailed project description, see https://github.com/it-at-m/eappointment

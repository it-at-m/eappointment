# ZMS HTTP messaging

Use this library to messaging email and notifications.

## Requirements

* PHP 5.4+

## Installation

Usually this module is required by other modules and does not need any special installation. Add the following lines to your composer.json:

```json
{
  "require": {
    "bo/zmsmessaging": "^1.*"
  }
}
```

## Usage

```php

```

## Testing

Testing is automated on committing changes. If you want to run the test without a commit, type the following:

    bin/test

If you want to view a coverage report, you need php-xdebug to generate the report with the following command:

    make coverage

The report is located under `./coverage/index.html`.

## Development

For development, additional modules are required. Commits from a live environment require to ignore the pre-commit hooks.

    make dev

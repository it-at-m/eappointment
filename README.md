# ZMS Entities

This repository contains schema defintions and entity classes to handle shared ZMS data types.

## Requirements

* PHP 5.4+

## Installation

Usually this module is required by other modules and does not need any special installation. Add the following lines to your composer.json:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "ssh://gitlab@gitlab.berlinonline.net:722/land-intranet/zmsentities.git"
    }
  ],
  "require": {
    "bo/zmsentities": "*"
  }
}
```

Change the `"*"` to an appropriate version placeholder.

## Development

For development, additional modules are required. Commits from a live environment require to ignore the pre-commit hooks.

    make dev
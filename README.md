# ZMS Administration

This application offers an administrative web frontend for statistics.

## Requirements

* PHP 5.4+

## Installation

The variable `$WEBROOT` represents the parent path to install the application.

```bash
    cd $WEBROOT
    git clone https://gitlab.berlinonline.net/land-intranet/zmsstatistic.git
    cd zmsstatistic
    make live
    cp config.example.php config.php
```

Edit the `config.php` and add/change settings for accessing the API.

To enable the application, you have to point the webserver to the public-path in the installation.
The following rewrite rules are required, examples for Apache2:

```apache
    RewriteRule ^/terminvereinbarung/statistic/_(.*) $WEBROOT/zmsstatistic/public/_$1
    RewriteRule ^/terminvereinbarung/statistic/(.*) $WEBROOT/zmsstatistic/public/index.php/$1
```


## Development

For development, additional modules are required. Commits from a live environment require to ignore the pre-commit hooks.

    make dev

## Testing

To test application run the following command:

    bin/test

## Add SCSS module

First add DEPENDENCY to bower

    ./node_modules/.bin/bower install --save-dev DEPENDENCY

Then add `@import()` rules to `scss/statistic.scss`.

Finally generate the CSS:

    make
    # or
    ./node_modules/.bin/gulp

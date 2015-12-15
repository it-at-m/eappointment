# ZMS Administration

This application offers an administrative web frontend as well as queue management for service agents.

## Requirements

* PHP 5.4+

## Installation

The variable `$WEBROOT` represents the parent path to install the application.

```bash
    cd $WEBROOT
    git clone https://gitlab.berlinonline.net/land-intranet/zmsadmin.git
    cd zmsadmin
    make live
    cp config.example.php config.php
```

Edit the `config.php` and add/change settings for accessing the API.

To enable the application, you have to point the webserver to the public-path in the installation.
The following rewrite rules are required, examples for Apache2:

```apache
    RewriteRule ^/terminvereinbarung/admin/_(.*) $WEBROOT/zmsadmin/public/_$1
    RewriteRule ^/terminvereinbarung/admin/(.*) $WEBROOT/zmsadmin/public/index.php/$1
```


## Development

For development, additional modules are required. Commits from a live environment require to ignore the pre-commit hooks.

    make dev

## Add SCSS module

First add DEPENDENCY to bower

    ./node_modules/.bin/bower install --save-dev DEPENDENCY

Then add `@import()` rules to `scss/admin.scss`.

Finally generate the CSS:

    make
    # or
    ./node_modules/.bin/gulp
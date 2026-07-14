# ZMS Admin

[![CI](https://github.com/it-at-m/eappointment/actions/workflows/combined-workflow-with-docs.yaml/badge.svg?branch=main)](https://github.com/it-at-m/eappointment/actions/workflows/combined-workflow-with-docs.yaml)
[![coverage report](https://img.shields.io/badge/coverage-report-blue)](https://it-at-m.github.io/eappointment/coverage/coverage-zmsadmin/html/)


# ZMS Administration

This application offers an administrative web frontend as well as queue management for managing appointments by using the zmsbackend REST API.

## Requirements

* PHP 7.3+

## Installation

The variable `$WEBROOT` represents the parent path to install the application.

```bash
    cd $WEBROOT
    git clone https://github.com/it-at-m/eappointment.git
    cd eappointment/zmsadmin
    make live
    cp config.example.php config.php
```

## Development

For development, additional modules are required. Commits from a live environment require to ignore the pre-commit hooks.
For local development and to compile public js and css files do

```bash
    ...
    make dev
    make build
    ...
```

## Configuration

Edit the `config.php` and add/change settings for accessing the API.

To enable the application, you have to point the webserver to the public-path in the installation.
The following rewrite rules are required, examples for Apache2 and nginx:

```apache
    RewriteRule ^/admin/_(.*)       $WEBROOT/zmsadmin/public/_$1
    RewriteRule ^/admin/(.*)        $WEBROOT/zmsadmin/public/index.php/$1
```

```nginx
    location ~ ^/(admin)/index\.php$ {
        fastcgi_pass    php-upstream;
        fastcgi_index   index.php;
        include         fastcgi_params;
        fastcgi_param   SCRIPT_FILENAME   $document_root$fastcgi_script_name;
        fastcgi_param   SERVER_NAME       $cgi_server_name;
        fastcgi_param   SERVER_PORT       $cgi_server_port;
        fastcgi_param   SERVER_PROTOCOL   $cgi_server_protocol;
        fastcgi_param   REQUEST_SCHEME    $cgi_server_protocol;
        fastcgi_param   HTTPS             $cgi_server_https if_not_empty;
    }

    location @rewrite {
        rewrite ^/admin/([^?]*)$    $WEBROOT/zmsadmin/index.php?/$1 last;
    }
```
    
## Testing

To test application run the following command:

    bin/test


For a detailed project description, see https://github.com/it-at-m/eappointment





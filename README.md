# Eappointment HTTP client

[![pipeline status](https://gitlab.com/eappointment/zmsclient/badges/main/pipeline.svg)](https://gitlab.com/eappointment/zmsclient/-/commits/main)
[![coverage report](https://gitlab.com/eappointment/zmsclient/badges/main/coverage.svg)](https://eappointment.gitlab.io/zmsclient/_tests/coverage/index.html)

Use this library to fetch data from the eappointment API via HTTP.

For a detailed project description, see https://gitlab.com/eappointment/eappointment

## Requirements

* PHP 7.3+

## Installation

Usually this module is required by other modules and does not need any special installation. Add the following lines to your composer.json:

```sh
composer require eappointment/zmsclient
```

## Usage

```php
$http = new \BO\Zmsclient\Http("https://eappointment.example.com/api/2");
$result = $http->readGetResult('/status/');
$entity = $result->getEntity();
var_dump($entity->version);
```

### Configuration

Setting up default CURL-Options, use the following line:

```php
\BO\Zmsclient\Psr7\Client::$curlopt = [
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 3,
    //CURLOPT_VERBOSE => true,
];
```

## Testing

Testing is automated on committing changes. If you want to run the test without a commit, type the following:

```sh
./bin/test
```

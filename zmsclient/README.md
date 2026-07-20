# Eappointment HTTP client

[![CI](https://github.com/it-at-m/eappointment/actions/workflows/combined-workflow-with-docs.yaml/badge.svg?branch=main)](https://github.com/it-at-m/eappointment/actions/workflows/combined-workflow-with-docs.yaml)
[![coverage report](https://img.shields.io/badge/coverage-report-blue)](https://it-at-m.github.io/eappointment/coverage/coverage-zmsclient/html/)

Use this library to fetch data from the eappointment API via HTTP.

For a detailed project description, see https://github.com/it-at-m/eappointment

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

If you want to run the test, Docker with the Compose V2 plugin (`docker compose`) is required. Testing needs an HTTP server to answer the HTTP calls from this library.

Run the following command:

```sh
docker compose up
```

Docker Compose starts the mockup server, waits 10 seconds and starts the unit tests. After the tests are finished, the mockup server is still running. If there are failures, you need to lookup possible HTTP calls. There is a port forwarding and you can see the calls under http://localhost:8082/


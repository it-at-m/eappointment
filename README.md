# ZMS HTTP client

Use this library to fetch data from the API via HTTP.

## Requirements

* PHP 5.4+

## Installation

Usually this module is required by other modules and does not need any special installation. Add the following lines to your composer.json:

```json
{
  "require": {
    "bo/zmsclient": "^1.*"
  }
}
```

## Usage

```php
$http = new \BO\Zmsclient\Http("https://eappointment.example.com/api/2");
$result = $http->readGetResult('/status/');
$entity = $result->getEntity();
var_dump($entity->version);
```

### Methods

```php
Http::readGetResult($relativeUrl, Array $getParameters = null)
```
* Do a HTTP-GET request


```php
Http::readPostResult($relativeUrl, \BO\Zmsentities\Schema\Entity $entity, Array $getParameters = null)
```
* Do a HTTP-POST request with an entity as request body


```php
Http::readDeleteResult($relativeUrl, Array $getParameters = null)
```
* Do a HTTP-DELETE request

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

    bin/test

If you want to view a coverage report, you need php-xdebug to generate the report with the following command:

    make coverage

The report is located under `./coverage/index.html`.

## Development

For development, additional modules are required. Commits from a live environment require to ignore the pre-commit hooks.

    make dev

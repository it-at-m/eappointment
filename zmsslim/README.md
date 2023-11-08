# Slimframework project base

[![pipeline status](https://gitlab.com/eappointment/zmsslim/badges/main/pipeline.svg)](https://gitlab.com/eappointment/zmsslim/-/commits/main)
[![coverage report](https://gitlab.com/eappointment/zmsslim/badges/main/coverage.svg)](https://eappointment.gitlab.io/zmsslim/_tests/coverage/index.html)


This module is intended to help with the creation of a Slim based framework application.

## Installation

Add these lines to your `composer.json` and run `composer.phar update`:

```json
    "require": {
       "eappointment/zmsslim": dev-main
    }
```

## Usage

The idea of this tool is based on implementing a slim framework in a way that the route bindings can be adjusted at any time without having to change the module. This repo should be integrated into your project via composer and initialized in the bootstrap.php of your project.

A global class `\App` is configured, which can be used to access a Slim instance.

### Access to Slim

It is possible to access Slim via `\App::$slim`. We do not recommend a direct access if possible.

To render output to view use the class `BO\Slim\Render` in which functions like `withHtml()` and `withJson()` are provided for example.

To render a route: 

```php
<?php 
use BO\Slim\Render;

class MyController extends BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $data = fetchMyDataById($args['id']);
        if (amIWrongHere($data)) {
            return Render::redirect('myotherroute', $args);
        }

        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
        return Render::withHtml(
            $response,
            'pathToTemplate.twig',
            array(
                'data' => $data
            )
        );
    }
}
```

Define a Basecontroller to initiate/prepare the request with multilingualism, if this is set in the configuration (Application.php). Also, the cache time of the response can be set here.

```php
<?php 
use BO\Slim\Render;

abstract class BaseController extends \BO\Slim\Controller
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $noCacheResponse = Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }
}
```

### Multilingualism

To activate multilingualism set MULTILANGUAGE to true. You can do this in config.php (recommended) or by default in Application.php. 

There is a Symfony based translation class which can use different loaders for the translation files. In our case "json" and "pofile" can be used. The usage is defined by the variable `$languagesource`. 

In the variable `$supportedLanguages` the individual languages are defined, which are to be offered in the application. The default language must always be defined, even if multilingualism is not to be used.


```php
<?php
    const MULTILANGUAGE = true;
    
    public static $languagesource = 'json';

    public static $language = null;

    public static $supportedLanguages = array(
        // Default language
        'de' => array(
            'name'    => 'Deutsch',
            'locale'  => 'de_DE',
            'default' => true,
        ),
        'en' => array(
            'name'    => 'English',
            'locale'  => 'en_GB',
            'default' => false,
        )
    );
```

The translation files are stored in the "lang" folder in the root directory. These must correspond to the format "locale.filetype". "locale" is the "locale" value, which is stored in the "$supportedLanguage" array. For example:

```php
de_DE.json
en_GB.json
```
json example:
```json
{
  "languageswitch": {
    "title": "language",
    "choose": "choose language",
    "languages": {
      "de": "Deutsch",
      "en": "English"
    }
  }
}
```
or
```php
de_DE.po
en_GB.po
```

po example:
```php
msgid "title"
msgstr "language"

msgid "choose"
msgstr "choose language"

msgid "de"
msgstr "Deutsch"

msgid "en"
msgstr "English"
```

### Logging

We use Monolog for logging. This logger implements the [PSR3 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md).

```php
<?php \App::$log->debug("My message", array($var1, $var2));
```

### Bootstrap and configuration

First, you should implement your own `class Application` as follows:

`src/MyApp/Application.php`:
```php
<?php

namespace `BO\MyApp

class Application extends \BO\Slim\Application
{
}
```

The application class contains the default settings for your application. 
The configuration is specific to your instance and should not be checked into your VCS:

`config.php`:
```php
<?php

class App extends \BO\MyApplication
{
    const SLIM_DEBUG = true;
    const TEMPLATE_PATH = '/templates/';
    const DEBUG = false;
    const DEBUGLEVEL = 'WARNING';
}
```

The initialization of your system is done in the bootstrap script:

`bootstrap.php`:
```php
<?php
// @codingStandardsIgnoreFile

// Define the application path as a single global constant
define("APP_PATH", realpath(__DIR__));

// use the autoloading provided by composer, see composer.json for path settings
require(APP_PATH . '/vendor/autoload.php');

// initialization of the static \App singleton
require(APP_PATH . '/config.php');

// set options for environment, routing, logging and templating
\BO\Slim\Bootstrap::init();

// Load routing
require(\App::APP_PATH . '/routing.php');
```

Routing is done in a special top-level routing script.
We recommend using a single file for easy searching of available routes.
Implement no or little logic in your routing, use references to existing classes:

`routing.php`:
```php
<?php

/* ---------------------------------------------------------------------------
 * html routes
 * -------------------------------------------------------------------------*/

\App::$slim->get('/',
    '\BO\MyAppController\Index')
    ->name("index");

/* ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------*/

\App::$slim->get('/healthcheck/', \BO\MyApp\Healthcheck::class)
    ->setName('healthcheck');

\App::$slim->getContainer()->offsetSet('notFoundHandler', function ($container) {
    return function (RequestInterface $request, ResponseInterface $response) {
        return \BO\Slim\Render::withHtml($response, 'page/404.twig');
    };
});

\App::$slim->getContainer()->offsetSet('errorHandler', function ($container) {
    return new \BO\MyApp\Handler\TwigExceptionHandler($container);
});
\App::$slim->getContainer()->offsetSet('phpErrorHandler', function ($container) {
    return new \BO\MyApp\Handler\TwigExceptionHandler($container);
});

```

Finally, you need an index.php. This should be located in a subdirectory called `public`.

`public/index.php`:
```php
<?php
// Do not add any functionality here
include('../bootstrap.php');
\App::$slim->run();
```

For nice URLs you need a `.htaccess` file if you use an Apache2 webserver:

`public/.htaccess`:
``` ApacheConf
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^_
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ /index.php [QSA,L]
```

## Twig integration

Our implementation of Slim uses Twig as the templating engine.
There are two options for configuration in the bootsrap file:

```php
<?php
    \BO\Slim\Bootstrap::init();
    \BO\Slim\Bootstrap::addTwigExtension(new \BO\MyApp\TwigExtension());
    \BO\Slim\Bootstrap::addTwigTemplateDirectory('dldb', APP_PATH . '/vendor/bo/clientdldb/templates');
```

Twig allows the use of multiple template directories. 
To add a template directory, you can use the `addTwigTemplateDirectory` function.
The first argument is a namespace, the second argument is a path to a directory.
To access a template in a different path, use a syntax like this:

```twig
{% include "@namepace/templatename.twig" %}
```

To extend Twig's capabilities, you can define your own functions.
Use the function `addTwigExtension()` to add an extension.
The first argument should be of type `\Slim\Views\TwigExtension`.

We implement a few predefined functions available in the Twig templates:

### urlGet
```php
<?php 
    public function urlGet($name, $params = array(), $getparams = array(), $appName = 'default')
```
Creates a URL for linking a defined route. 
It allows adding GET parameters to the URL.

* **name** *String* - name of the route, see routing.php.
* **params** *Array* - list of parameters for routes defined like "name" in "/user/:name/detail".
* **getparams** *Array* - list of parameters to add, like "name" in "/myuri?name=dummy"
* **appName** *String* - see slim documentation, not supported.

### csvProperty
```php
<?php 
    public function csvProperty($list, $property)
```
Allows extracting a property as a csv from a list of arrays.
For example, if you have a list of entities and need the IDs as parameters for a URL.

* **List** *Array* - Array to extract the property from.
**Property** *String* - name of the property to be extracted

### azPrefixList
```php
<?php 
    public function azPrefixList($list, $property)
```
To create A-Z lists, you need to group a list by a property.

* **list** *array* - array from which the property is extracted.
* **property** *String* - name of the property by which the list should be grouped, first character.

### isValueInArray
```php
<?php 
    public function isValueInArray($value, $params)
```
Checks if a value is a CSV. Helpful in combination with csvProperty.

* **value** *String* - value to check for.
* **params** *String* - Comma separated values

### remoteInclude
```php
<?php 
    public static function remoteInclude($uri)
```
Includes a remote HTML file in your code.
If the configuration setting `ESI_ENABLED` is true, it will output a `<esi:include src="$uri" />`.

* **uri** *String* - URL to include in the template.


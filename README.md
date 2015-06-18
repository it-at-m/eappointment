This modul is intended to help building a slim application.
The provided functions help implementing some common practices.

## Installation

Add these lines to your `composer.json` and run `composer.phar update`:

```json
    "require": {
        "bo/slimproject": "dev-master"
    }
```

## Usage

The philosophy behind this modul is to implement Slim in a way to ensure you can change your router implementation later.

At first you need some configuration. 
The implementations of this modul need a global class `\App` to offer the behaviour.
At first you should implement you own `class Application` like this:

`src/MyApp/Application.php`:
```php
<?php

namespace \BO\MyApp

class Application extends \BO\Slim\Application
{
}
```

The application class contains your default settings for your application. 
The configuration is specific for your instance and should not be checked into your VCS:

`config.php`:
```php
<?php

class App extends \BO\MyApp\Application
{
    const SLIM_DEBUG = true;
    const TEMPLATE_PATH = '/templates/';
    const MONOLOG_LOGLEVEL = \Monolog\Logger::WARNING;
}
```

The initialization of your system happens in the bootstrap script:

`bootstrap.php`:
```php
<?php
// @codingStandardsIgnoreFile

// define the application path as single global constant
define("APP_PATH", realpath(__DIR__));

// use autoloading offered by composer, see composer.json for path settings
require(APP_PATH . '/vendor/autoload.php');

// initialize the static \App singleton
require(APP_PATH . '/config.php');

// Set option for environment, routing, logging and templating
\BO\Slim\Bootstrap::init();

// load routing
require(\App::APP_PATH . '/routing.php');
```

Routing happens in a special top level routing script.
We recommend using one file for an easy search for available routes.
Do not implement (much) behaviour in your routing, use references to existing classes:

`routing.php`:
```php
<?php
// @codingStandardsIgnoreFile
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

/* ---------------------------------------------------------------------------
 * html routes
 * -------------------------------------------------------------------------*/

\App::$slim->get('/',
    '\BO\MyApp\Controller\Index:render')
    ->name("index");

/* ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------*/

\App::$slim->get('/healthcheck/',
    '\BO\MyApp\Controller\Healthcheck:render')
    ->name("healthcheck");

\App::$slim->notfound(function () {
    \BO\Slim\Render::html('404.twig');
});

\App::$slim->error(function (\Exception $exception) {
    \BO\Slim\Render::lastModified(time(), '0');
    \BO\Slim\Render::html('failed.twig', array(
        "failed" => $exception->getMessage(),
        "error" => $exception,
    ));
    \App::$slim->stop();
});
```

At last you need an index.php. This should be in a sub directory called `public`.

`public/index.php`:
```php
<?php
// Do not add any functionality here
include('../bootstrap.php');
\App::$slim->run();
```

For nice URLs you want an `.htaccess` file if you use an apache2 webserver:

`public/.htaccess`:
```ApacheConf
RewriteEngine On
RewriteCond %{REQUEST_URI} !^_
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ /index.php [QSA,L]
```

## Twig integration

Our implementation of Slim uses Twig as templating engine.
There are two options for configuration in the bootsrap file:

```php
<?php
\BO\Slim\Bootstrap::init();
\BO\Slim\Bootstrap::addTwigExtension(new \BO\MyApp\TwigExtension());
\BO\Slim\Bootstrap::addTwigTemplateDirectory('dldb', APP_PATH . '/vendor/bo/clientdldb/templates');
```

Twig allows to use multiple template directories. 
To add a template directory, you can use the function `addTwigTemplateDirectory`.
The first argument is a namespace, the second argument a path to a directory.
To access a template in another path, use a syntax like this:

```twig
{% include "@namespace/templatename.twig" %}
```

To extend the possibilities with Twig, you can define custom function.
Use the function `addTwigExtension()` to add an extension.
The first argument should be of the type `\Slim\Views\TwigExtension`.

We implement a few predefined functions available in the twig templates:

### urlGet
```php
<?php public function urlGet($name, $params = array(), $getparams = array(), $appName = 'default')
```
Generate an URL for linking a defined route. 
It allows to add GET-parameters to the URL.

* **name** *String* - Name of the route, see routing.php
* **params** *Array* - List of parameters for routes defined like "name" in "/user/:name/detail"
* **getparams** *Array* - List of parameters to add like "name" in "/myuri?name=dummy"
* **appName** *String* - see Slim documentation, not supported

### csvProperty
```php
<?php public function csvProperty($list, $property)
```
Allows to extract a property as csv from a list of arrays.
For example if you a have a list of entities and you need the IDs as parameter for an URL.

* **list** *Array* - Array to extract property from
* **property** *String* - Name of the property to extract

### azPrefixList
```php
<?php public function azPrefixList($list, $property)
```
To generate A-Z Lists you need to group a list by a property.

* **list** *Array* - Array to extract property from
* **property** *String* - Name of the property to group the list by the first character

### isValueInArray
```php
<?php public function isValueInArray($value, $params)
```
Check, if a value is an CSV. Helpful in combination with csvProperty.

* **value** *String* - Value to check for
* **params** *String* - Comma seperated values

### remoteInclude
```php
<?php public static function remoteInclude($uri)
```
Include a remote html file into your code.
If the config setting `ESI_ENABLED` is true, it will output an `<esi:include src="$uri" />`.

* **uri** *String* - URL to include in the template
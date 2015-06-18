This modul is intended to help building a slim application.
The provided functions help implementing some common practices.

## Usage

The philosophy behind this modul is to implement Slim in a way to ensure you can change your router implementation later.

At first you need some configuration. 
The implementations of this modul need a global class \App to offer the behaviour.
At first you should implement you own class Application like this:

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
\BO\Slim\Bootstrap::addTwigExtension(new \BO\Dldb\TwigExtension());
\BO\Slim\Bootstrap::addTwigTemplateDirectory('dldb', APP_PATH . '/vendor/bo/clientdldb/templates');

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
    \BO\Serviceportal\Controller\Helper\Render::html('404.twig');
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
```htaccess
RewriteEngine On
RewriteCond %{REQUEST_URI} !^_
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ /index.php [QSA,L]
```
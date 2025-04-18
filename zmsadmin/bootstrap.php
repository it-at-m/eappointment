<?php
// phpcs:disable PSR1.Files.SideEffects
// define the application path as single global constant
define("APP_PATH", realpath(__DIR__));

chdir(__DIR__);

// use autoloading offered by composer, see composer.json for path settings
if (file_exists(APP_PATH . '/vendor/autoload.php')) {
    define('VENDOR_PATH', APP_PATH . '/vendor');
} else {
    define('VENDOR_PATH', APP_PATH . '/../..');
}
require_once(VENDOR_PATH . '/autoload.php');

// initialize the static \App singleton
require(APP_PATH . '/config.php');

// Set option for environment, routing, logging and templating
\BO\Slim\Bootstrap::init();
\BO\Slim\Bootstrap::addTwigExtension(new \Twig\Extra\Intl\IntlExtension());

// Initialize application
\App::initialize();

// Initialize cache
$cache = new \Symfony\Component\Cache\Psr16Cache(
    new \Symfony\Component\Cache\Adapter\FilesystemAdapter(
        namespace: \App::MODULE_NAME,
        defaultLifetime: \App::$PSR16_CACHE_TTL_ZMSADMIN,
        directory: \App::$PSR16_CACHE_DIR_ZMSADMIN
    )
);
\App::$cache = $cache;


\BO\Mellon\ValidMail::$disableDnsChecks = true;

\App::$http = new \BO\Zmsclient\Http(\App::HTTP_BASE_URL);
\BO\Zmsclient\Psr7\Client::$curlopt = \App::$http_curl_config;

// Http Logging
\BO\Slim\Bootstrap::addTwigExtension(new \BO\Zmsclient\TwigExtension(\App::$slim->getContainer()));
\BO\Zmsclient\Http::$logEnabled = \App::DEBUG;
\BO\Zmsclient\Http::$jsonCompressLevel = \App::JSON_COMPRESS_LEVEL;

// Templating
\BO\Slim\Bootstrap::addTwigTemplateDirectory('zmsentities', \BO\Zmsentities\Helper\TemplateFinder::getTemplatePath());

// add slim middleware
$errorMiddleware = \App::$slim->getContainer()->get('errorMiddleware');
$errorMiddleware->setDefaultErrorHandler(new \BO\Zmsadmin\Helper\TwigExceptionHandler());

// load routing
\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php');

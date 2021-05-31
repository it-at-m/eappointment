<?php
// @codingStandardsIgnoreFile

// ini_set('xdebug.auto_trace', true);
// ini_set('xdebug.trace_output_dir', './');
ini_set('session.serialize_handler', 'php_serialize');

// define the application path as single global constant
define("APP_PATH", realpath(__DIR__));

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
\BO\Slim\Bootstrap::addTwigExtension(new \Twig\Extensions\TextExtension());
\BO\Slim\Bootstrap::addTwigExtension(new \Twig\Extensions\I18nExtension());
\BO\Slim\Bootstrap::addTwigExtension(new \Twig\Extensions\IntlExtension());

\App::$http = new \BO\Zmsclient\Http(\App::HTTP_BASE_URL);
\BO\Zmsclient\Psr7\Client::$curlopt = \App::$http_curl_config;

// Http API Logging
\BO\Slim\Bootstrap::addTwigExtension(new \BO\Zmsclient\TwigExtension(\App::$slim->getContainer()));
\BO\Zmsclient\Http::$logEnabled = \App::DEBUG;
\BO\Zmsclient\Http::$jsonCompressLevel = \App::JSON_COMPRESS_LEVEL;


\App::$now = (\App::$now) ? \App::$now : new \DateTimeImmutable();

// load middleware
\App::$slim->add(new \BO\Slim\Middleware\TrailingSlash());

// load routing
\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php');

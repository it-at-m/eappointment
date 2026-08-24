<?php
// @codingStandardsIgnoreFile
chdir(__DIR__);
$appPath = realpath(__DIR__);
if ($appPath === false) {
    throw new \RuntimeException('Cannot resolve application path');
}
define("APP_PATH", $appPath);

// use autoloading offered by composer, see composer.json for path settings
if (file_exists(APP_PATH . '/vendor/autoload.php')) {
    define('VENDOR_PATH', APP_PATH . '/vendor');
} else {
    define('VENDOR_PATH', APP_PATH . '/../..');
}
/** @psalm-suppress UnresolvableInclude */
require_once(VENDOR_PATH . '/autoload.php');

// initialize the static \App singleton
/** @psalm-suppress UnresolvableInclude */
require(APP_PATH . '/config.php');

// Set option for environment, routing, logging and templating
\BO\Slim\Bootstrap::init();
\BO\Slim\Helper\ModuleLoggerInitializer::registerHttpMiddleware(false);
\BO\Slim\Bootstrap::addTwigExtension(new Twig\Extra\Intl\IntlExtension());

\App::$http = new \BO\Zmsclient\Http(\App::HTTP_BASE_URL);
\BO\Zmsclient\Psr7\Client::$curlopt = \App::$http_curl_config;

$container = \App::$slim->getContainer();
if ($container === null) {
    throw new \RuntimeException('Slim container is not initialized');
}

// Http Logging
\BO\Slim\Bootstrap::addTwigExtension(new \BO\Zmsclient\TwigExtension($container));
\BO\Zmsclient\Http::$logEnabled = \App::DEBUG;
\BO\Zmsclient\Http::$jsonCompressLevel = \App::JSON_COMPRESS_LEVEL;

// load error middleware
$errorMiddleware = $container->get('errorMiddleware');
$errorMiddleware->setDefaultErrorHandler(new \BO\Zmsstatistic\Helper\TwigExceptionHandler());

// load routing
\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php');

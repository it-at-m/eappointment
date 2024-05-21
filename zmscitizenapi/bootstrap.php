<?php
// @codingStandardsIgnoreFile
chdir(__DIR__);

// define the application path as single global constant
if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__));
}

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

\App::$http = new \BO\Zmsclient\Http(\App::ZMS_API_URL);
//\BO\Zmsclient\Psr7\Client::$curlopt = \App::$http_curl_config;

//$errorMiddleware = \App::$slim->getContainer()->get('errorMiddleware');
//$errorMiddleware->setDefaultErrorHandler(new \BO\Zmscitizenapi\Helper\ErrorHandler());

// load routing
\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php');

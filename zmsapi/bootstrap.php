<?php
// @codingStandardsIgnoreFile

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
require_once(APP_PATH . '/config.php');

\BO\Slim\Bootstrap::init();

// Set option for environment, routing, logging and templating
\BO\Zmsdb\Connection\Select::$enableProfiling = \App::DEBUG;
\BO\Zmsdb\Connection\Select::$readSourceName = \App::DB_DSN_READONLY;
\BO\Zmsdb\Connection\Select::$writeSourceName = \App::DB_DSN_READWRITE;
\BO\Zmsdb\Connection\Select::$username = \App::DB_USERNAME;
\BO\Zmsdb\Connection\Select::$password = \App::DB_PASSWORD;
\BO\Zmsdb\Connection\Select::$galeraConnection = \App::DB_IS_GALERA;
\BO\Zmsdb\Connection\Select::$pdoOptions = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
];
\BO\Zmsdb\Connection\Select::$connectionTimezone = ' ' . \App::$now->getTimezone()->getName();

\App::$slim->add(new \BO\Zmsapi\Helper\TransactionMiddleware());
\App::$slim->add(new \BO\Zmsapi\Helper\LogOperatorMiddleware());

// add slim error middleware
$errorMiddleware = \App::$slim->getContainer()->get('errorMiddleware');
$errorMiddleware->setDefaultErrorHandler(new \BO\Zmsapi\Helper\ErrorHandler());

// DLDB data loader
\BO\Zmsdb\Source\Dldb::$importPath = \App::APP_PATH . \App::$data;

// load routing
\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php');
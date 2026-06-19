<?php
// @codingStandardsIgnoreFile

if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__));
}

if (file_exists(APP_PATH . '/vendor/autoload.php')) {
    define('VENDOR_PATH', APP_PATH . '/vendor');
} else {
    define('VENDOR_PATH', APP_PATH . '/../..');
}
require_once(VENDOR_PATH . '/autoload.php');

require_once(APP_PATH . '/config.php');

\BO\Slim\Bootstrap::init();

\BO\Zmsbackend\Connection\Select::$enableProfiling = \App::DEBUG;
\BO\Zmsbackend\Connection\Select::$readSourceName = \App::DB_DSN_READONLY;
\BO\Zmsbackend\Connection\Select::$writeSourceName = \App::DB_DSN_READWRITE;
\BO\Zmsbackend\Connection\Select::$username = \App::DB_USERNAME;
\BO\Zmsbackend\Connection\Select::$password = \App::DB_PASSWORD;
\BO\Zmsbackend\Connection\Select::$galeraConnection = \App::DB_IS_GALERA;
\BO\Zmsbackend\Connection\Select::$pdoOptions = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
];
\BO\Zmsbackend\Connection\Select::$connectionTimezone = ' ' . \App::$now->getTimezone()->getName();

$logger = new \BO\Slim\LoggerService();
$requestLimits = \App::getRequestLimits();

\App::$slim->add(new \BO\Slim\Middleware\RequestLoggingMiddleware($logger));
\App::$slim->add(new \BO\Slim\Middleware\SecurityHeadersMiddleware($logger));
\App::$slim->add(new \BO\Slim\Middleware\RequestSanitizerMiddleware(
    $logger,
    $requestLimits['maxRecursionDepth'],
    $requestLimits['maxStringLength']
));
\App::$slim->add(new \BO\Zmsbackend\Helper\TransactionMiddleware());
\App::$slim->add(new \BO\Zmsbackend\Helper\LogOperatorMiddleware());

if (\App::$slim->getContainer()->has('errorMiddleware')) {
    $errorMiddleware = \App::$slim->getContainer()->get('errorMiddleware');
    $errorMiddleware->setDefaultErrorHandler(new \BO\Zmsbackend\Helper\ErrorHandler());
}

\BO\Zmsbackend\Source\Zmsdldb::$importPath = \App::APP_PATH . \App::$data;

\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php');

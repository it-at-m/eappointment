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

// Initialize App::$now if not already set
\App::$now = (\App::$now instanceof \DateTimeInterface) ? \App::$now : new \DateTimeImmutable();

// Set option for environment, routing, logging and templating
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
if (defined('MYSQL_DATABASE')) {
    \BO\Zmsbackend\Connection\Select::$dbname_zms = MYSQL_DATABASE;
}
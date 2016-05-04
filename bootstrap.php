<?php
// @codingStandardsIgnoreFile

// define the application path as single global constant
define("APP_PATH", realpath(__DIR__));

// use autoloading offered by composer, see composer.json for path settings
require(APP_PATH . '/vendor/autoload.php');

// initialize the static \App singleton
require(APP_PATH . '/config.php');

\BO\Slim\Bootstrap::init();
\BO\Slim\Bootstrap::addTwigTemplateDirectory('dldb', APP_PATH . '/vendor/bo/clientdldb/templates');

// Set option for environment, routing, logging and templating
\BO\Zmsdb\Connection\Select::$enableProfiling = \APP::DEBUG;
\BO\Zmsdb\Connection\Select::$readSourceName = \APP::DB_DSN_READONLY;
\BO\Zmsdb\Connection\Select::$writeSourceName = \APP::DB_DSN_READWRITE;
\BO\Zmsdb\Connection\Select::$username = \APP::DB_USERNAME;
\BO\Zmsdb\Connection\Select::$password = \APP::DB_PASSWORD;
\BO\Zmsdb\Connection\Select::$pdoOptions = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
];

//use fixtures path for developement
\BO\Zmsdb\Helper\DldbData::$dataPath = \App::APP_PATH . '/vendor/bo/zmsdb/tests/Zmsdb/fixtures';
//\BO\Zmsdb\Helper\DldbData::$dataPath = \App::APP_PATH . \App::$data;

// load routing
\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php');

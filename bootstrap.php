<?php
// @codingStandardsIgnoreFile

// define the application path as single global constant
define("APP_PATH", realpath(__DIR__));

// use autoloading offered by composer, see composer.json for path settings
require(APP_PATH . '/vendor/autoload.php');

// initialize the static \App singleton
require(APP_PATH . '/config.php');

\BO\Slim\Bootstrap::init();
\BO\Slim\Bootstrap::addTwigExtension(new \BO\Slim\TwigExtension());
\BO\Slim\Bootstrap::addTwigExtension(new \Twig_Extensions_Extension_Text());
\BO\Slim\Bootstrap::addTwigExtension(new \Twig_Extensions_Extension_I18n());
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

// configure clientdldb data access
\App::$dldbdata = new \BO\Dldb\FileAccess(\App::$locale);
\App::$dldbdata->loadFromPath(\App::APP_PATH . \App::$data);

// load routing
require(\App::APP_PATH . '/routing.php');

<?php
\setlocale(LC_ALL, 'de_DE.utf-8');
\date_default_timezone_set('Europe/Berlin');

if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__));
}

if (file_exists(APP_PATH . '/../vendor/autoload.php')) {
    define('VENDOR_PATH', APP_PATH . '/../vendor');
} else {
    define('VENDOR_PATH', APP_PATH . '/../../../');
}
require_once(VENDOR_PATH . '/autoload.php');

// initialize the static \App singleton
require(APP_PATH . '/Slim/Config.php');


// Set option for environment, routing, logging and templating
\BO\Slim\Bootstrap::init();
\BO\Slim\Bootstrap::addTwigExtension(new \BO\Slim\TwigExtension(\App::$slim->getContainer()));

// load routing
\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/Slim/routing.php');

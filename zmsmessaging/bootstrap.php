<?php
// @codingStandardsIgnoreFile
// define the application path as single global constant
if (!defined('APP_PATH')) {
    define("APP_PATH", realpath(__DIR__));
}

chdir(__DIR__);
// use autoloading offered by composer, see composer.json for path settings
if (!defined('VENDOR_PATH')) {
if (file_exists(APP_PATH . '/vendor/autoload.php')) {
    define('VENDOR_PATH', APP_PATH . '/vendor');
} else {
    define('VENDOR_PATH', APP_PATH . '/../..');
}
}
require_once(VENDOR_PATH . '/autoload.php');

// initialize the static \App singleton
require(APP_PATH . '/config.php');

\App::$log = new \Monolog\Logger('Zmsmessaging');
\App::$now = (\App::$now instanceof \DateTimeInterface) ? \App::$now : new \DateTimeImmutable();
\App::$http = new \BO\Zmsclient\Http(\App::HTTP_BASE_URL);
\App::$http->setUserInfo(\App::$httpUser, \App::$httpPassword);
\BO\Zmsclient\Psr7\Client::$curlopt = \App::$http_curl_config;



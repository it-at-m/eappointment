<?php
// @codingStandardsIgnoreFile

if (!defined('MYSQL_USER')) {
    define('MYSQL_USER', getenv('MYSQL_USER') ? getenv('MYSQL_USER') : 'root');
}
if (!defined('MYSQL_PASSWORD')) {
    define('MYSQL_PASSWORD', getenv('MYSQL_PASSWORD') ? getenv('MYSQL_PASSWORD') : 'zmsapi');
}
if (!defined('MYSQL_DATABASE')) {
    define('MYSQL_DATABASE', getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') : 'zmsbo');
}
if (getenv('MYSQL_PORT')) {
    $dsn = "mysql:dbname=" . MYSQL_DATABASE . ";host=";
    $dsn .= parse_url(getenv('MYSQL_PORT'), PHP_URL_HOST);
    $dsn .= ';port=';
    $dsn .= parse_url(getenv('MYSQL_PORT'), PHP_URL_PORT);
    if (!defined('DSN_RW')) {
        define('DSN_RW', $dsn);
    }
} else {
    if (!defined('DSN_RW')) {
        define('DSN_RW', 'mysql:dbname=' . MYSQL_DATABASE . ';host=127.0.0.1');
    }
}
if (getenv('MYSQL_PORT_RO')) {
    $mysqlPortList = explode(',', getenv('MYSQL_PORT_RO'));
    $mysqlPortRO = trim($mysqlPortList[array_rand($mysqlPortList)]);
    $dsn = "mysql:dbname=" . MYSQL_DATABASE . ";host=";
    $dsn .= parse_url($mysqlPortRO, PHP_URL_HOST);
    $dsn .= ';port=';
    $dsn .= parse_url($mysqlPortRO, PHP_URL_PORT);
    if (!defined('DSN_RO')) {
        define('DSN_RO', $dsn);
    }
} else {
    if (!defined('DSN_RO')) {
        define('DSN_RO', DSN_RW);
    }
}

define('ZMS_IDENTIFIER', getenv('ZMS_IDENTIFIER') ? getenv('ZMS_IDENTIFIER') : 'zms');
define('ZMS_MODULE_NAME', 'zmsbackend');
$value = getenv('ZMS_BACKEND_TWIG_CACHE');
if ($value === false || $value === '') {
    $value = getenv('ZMS_API_TWIG_CACHE');
}
define('ZMS_BACKEND_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));

class App extends \BO\Zmsbackend\Application
{
    const APP_PATH = APP_PATH;
    const IDENTIFIER = ZMS_IDENTIFIER;
    const DEBUG = false;
    const DB_ENABLE_WSREPSYNCWAIT = true;
    const DB_DSN_READONLY = DSN_RO;
    const DB_DSN_READWRITE = DSN_RW;
    const DB_USERNAME = MYSQL_USER;
    const DB_PASSWORD = MYSQL_PASSWORD;
    const TWIG_CACHE = ZMS_BACKEND_TWIG_CACHE;
    const MODULE_NAME = ZMS_MODULE_NAME;
}

if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}

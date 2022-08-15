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

define('MYSQL_DATABASE', getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') : 'zms');
// Use Docker environment settings, if exists
if (getenv('MYSQL_PORT')) {
    $host = parse_url(getenv('MYSQL_PORT'), PHP_URL_HOST);
    $host .= ';port=';
    $host .= parse_url(getenv('MYSQL_PORT'), PHP_URL_PORT);
}
if (getenv('MYSQL_PASSWORD') || getenv('MYSQL_ROOT_PASSWORD')) {
    \BO\Zmsdb\Connection\Select::$username =
        getenv('MYSQL_USER') ? getenv('MYSQL_USER') : 'root';
    \BO\Zmsdb\Connection\Select::$password =
        getenv('MYSQL_ROOT_PASSWORD') ? getenv('MYSQL_ROOT_PASSWORD') : getenv('MYSQL_PASSWORD');
}
\BO\Zmsdb\Connection\Select::$enableProfiling = true;
\BO\Zmsdb\Connection\Select::$dbname_zms = constant("MYSQL_DATABASE");
if (getenv('MYSQL_PORT') && (getenv('MYSQL_PASSWORD') || getenv('MYSQL_ROOT_PASSWORD'))) {
    \BO\Zmsdb\Connection\Select::$readSourceName  = "mysql:dbname=" . \BO\Zmsdb\Connection\Select::$dbname_zms . ";host=$host";
    \BO\Zmsdb\Connection\Select::$writeSourceName = "mysql:dbname=" . \BO\Zmsdb\Connection\Select::$dbname_zms . ";host=$host";
}
\BO\Zmsdb\Connection\Select::$pdoOptions = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
];
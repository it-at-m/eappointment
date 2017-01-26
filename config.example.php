<?php
// @codingStandardsIgnoreFile
//ONLY FOR TESTING

require(__DIR__ . '/vendor/autoload.php');
date_default_timezone_set('Europe/Berlin');

define('MYSQL_DATABASE', getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') : 'zmsbo');
// Use Docker environment settings, if exists
if (getenv('MYSQL_PORT')) {
    $host = parse_url(getenv('MYSQL_PORT'), PHP_URL_HOST);
    $host .= ';port=';
    $host .= parse_url(getenv('MYSQL_PORT'), PHP_URL_PORT);
} else {
    $host = '127.0.0.1';
}
if (getenv('MYSQL_ENV_MYSQL_ROOT_PASSWORD') || getenv('MYSQL_ROOT_PASSWORD')) {
    \BO\Zmsdb\Connection\Select::$username = 'root';
    \BO\Zmsdb\Connection\Select::$password =
        getenv('MYSQL_ROOT_PASSWORD') ? getenv('MYSQL_ROOT_PASSWORD') : getenv('MYSQL_ENV_MYSQL_ROOT_PASSWORD');
} else {
    \BO\Zmsdb\Connection\Select::$username = 'server';
    \BO\Zmsdb\Connection\Select::$password = 'internet';
}
\BO\Zmsdb\Connection\Select::$enableProfiling = true;
\BO\Zmsdb\Connection\Select::$dbname_zms = constant("MYSQL_DATABASE");
\BO\Zmsdb\Connection\Select::$dbname_dldb = 'startinfo';
\BO\Zmsdb\Connection\Select::$readSourceName = "mysql:dbname=".\BO\Zmsdb\Connection\Select::$dbname_zms.";host=$host";
\BO\Zmsdb\Connection\Select::$writeSourceName = "mysql:dbname=".\BO\Zmsdb\Connection\Select::$dbname_zms.";host=$host";
\BO\Zmsdb\Connection\Select::$pdoOptions = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
];

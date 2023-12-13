<?php
// @codingStandardsIgnoreFile
//ONLY FOR TESTING

require(__DIR__ . '/vendor/autoload.php');
date_default_timezone_set('Europe/Berlin');

define('MYSQL_DATABASE', getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') : 'zmsbo');
// Use Docker environment settings, if exists
// Check for MYSQL_HOST first
if (getenv('MYSQL_HOST')) {
    $host = getenv('MYSQL_HOST') ?: '127.0.0.1';
    $port = getenv('MYSQL_PORT') ?: '3306'; // default MySQL port
    $host .= ";port=" . $port;
} elseif (getenv('MYSQL_PORT')) {
    // If MYSQL_PORT is set, parse it to get the host and port
    $host = parse_url(getenv('MYSQL_PORT'), PHP_URL_HOST);
    $host .= ';port=';
    $host .= parse_url(getenv('MYSQL_PORT'), PHP_URL_PORT);
} else {
    // Default fallback
    $host = '127.0.0.1;port=3306';
}
if (getenv('MYSQL_PASSWORD') || getenv('MYSQL_ROOT_PASSWORD')) {
    \BO\Zmsdb\Connection\Select::$username = 
        getenv('MYSQL_USER') ? getenv('MYSQL_USER') : 'root';
    \BO\Zmsdb\Connection\Select::$password =
        getenv('MYSQL_ROOT_PASSWORD') ? getenv('MYSQL_ROOT_PASSWORD') : getenv('MYSQL_PASSWORD');
} else {
    \BO\Zmsdb\Connection\Select::$username = 'server';
    \BO\Zmsdb\Connection\Select::$password = 'internet';
}
\BO\Zmsdb\Connection\Select::$enableProfiling = true;
\BO\Zmsdb\Connection\Select::$dbname_zms = constant("MYSQL_DATABASE");
\BO\Zmsdb\Connection\Select::$readSourceName = "mysql:dbname=".\BO\Zmsdb\Connection\Select::$dbname_zms.";host=$host";
\BO\Zmsdb\Connection\Select::$writeSourceName = "mysql:dbname=".\BO\Zmsdb\Connection\Select::$dbname_zms.";host=$host";
\BO\Zmsdb\Connection\Select::$pdoOptions = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
];

\BO\Zmsdb\Source\Dldb::$importPath = 'tests/Zmsdb/fixtures/';

if (getenv('ZMS_TIMEADJUST')) {

    class App {
        public static $now;
    }
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}

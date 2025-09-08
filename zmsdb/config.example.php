<?php
// @codingStandardsIgnoreFile
//ONLY FOR TESTING

require(__DIR__ . '/vendor/autoload.php');
date_default_timezone_set('Europe/Berlin');

define('MYSQL_DATABASE', getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') : 'zmsbo');
define('ZMS_IDENTIFIER', getenv('ZMS_IDENTIFIER') ? getenv('ZMS_IDENTIFIER') : 'zms');
define('ZMS_MODULE_NAME', 'zmsdb');

// Use Docker environment settings, if exists
if (getenv('MYSQL_PORT')) {
    $host = parse_url(getenv('MYSQL_PORT'), PHP_URL_HOST);
    $host .= ';port=';
    $host .= parse_url(getenv('MYSQL_PORT'), PHP_URL_PORT);
} else {
    $host = '127.0.0.1';
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

\BO\Zmsdb\Source\Zmsdldb::$importPath = realpath(dirname(__FILE__) . '/tests/Zmsdb/fixtures/');

class App extends \BO\Zmsdb\Application {
    /**
     * Name of the module
     */
    const IDENTIFIER = ZMS_IDENTIFIER;
    const ZMS_MODULE_NAME = ZMS_MODULE_NAME;
    public static $now;
    public static $log;
}
if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}

// Initialize logger (similar to zmsapi)
if (!App::$log) {
    // Use Monolog, log to stdout instead of file
    App::$log = new Monolog\Logger('zmsdb');
    App::$log->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Logger::INFO));
}

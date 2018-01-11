<?php
// @codingStandardsIgnoreFile

// Allow configuration by ENVIRONMENT variables

// MYSQL_USER with access to DB
define('MYSQL_USER', getenv('MYSQL_USER') ? getenv('MYSQL_USER') : 'root');
// MYSQL_PASSWORD
define('MYSQL_PASSWORD', getenv('MYSQL_PASSWORD') ? getenv('MYSQL_PASSWORD') : 'zmsapi');
// MYSQL_DATABASE is the database name containing the tables
define('MYSQL_DATABASE', getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') : 'zmsbo');
// MYSQL_PORT of type "tcp://127.0.0.1:3306"
if (getenv('MYSQL_PORT')) {
    $dsn = "mysql:dbname=" . MYSQL_DATABASE . ";host=";
    $dsn .= parse_url(getenv('MYSQL_PORT'), PHP_URL_HOST);
    $dsn .= ';port=';
    $dsn .= parse_url(getenv('MYSQL_PORT'), PHP_URL_PORT);
    define('DSN_RW', $dsn);
} else {
    define('DSN_RW', 'mysql:dbname=' . MYSQL_DATABASE . ';host=127.0.0.1');
}
// MYSQL_PORT_RO for readonly access of type "tcp://127.0.0.1:3306"
if (getenv('MYSQL_PORT_RO')) {
    $dsn = "mysql:dbname=" . MYSQL_DATABASE . ";host=";
    $dsn .= parse_url(getenv('MYSQL_PORT_RO'), PHP_URL_HOST);
    $dsn .= ';port=';
    $dsn .= parse_url(getenv('MYSQL_PORT_RO'), PHP_URL_PORT);
    define('DSN_RO', $dsn);
} else {
    define('DSN_RO', DSN_RW);
}

class App extends \BO\Zmsapi\Application
{
    const APP_PATH = APP_PATH;
    const IDENTIFIER = 'Zmsapi-ENV';
    const DEBUG = false;

    /**
     * @var String DB_DSN_READONLY
     */
    const DB_DSN_READONLY = DSN_RO;

    /**
     * @var String DB_DSN_READWRITE
     */
    const DB_DSN_READWRITE = DSN_RW;

    /**
     * @var String DB_USERNAME
     */
    const DB_USERNAME = MYSQL_USER;

    /**
     * @var String DB_PASSWORD
     */
    const DB_PASSWORD = MYSQL_PASSWORD;

    /**
     * Use caching
     *
     */
    const TWIG_CACHE = '/cache/';

    // Uncomment the following line for testing with fixtures
    // public static $data = "/vendor/bo/zmsdb/tests/Zmsdb/fixtures";
}

// Uncomment the following line for testing data with vendor/bin/importTestData
if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}

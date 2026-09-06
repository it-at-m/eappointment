<?php
// @codingStandardsIgnoreFile

// MYSQL_USER with access to DB
if (!defined('MYSQL_USER')) {
    /** @psalm-suppress RiskyTruthyFalsyComparison */
    define('MYSQL_USER', getenv('MYSQL_USER') ? getenv('MYSQL_USER') : 'root');
}
// MYSQL_PASSWORD
if (!defined('MYSQL_PASSWORD')) {
    /** @psalm-suppress RiskyTruthyFalsyComparison */
    define('MYSQL_PASSWORD', getenv('MYSQL_PASSWORD') ? getenv('MYSQL_PASSWORD') :'zmsbackend');
}
// MYSQL_DATABASE is the database name containing the tables
if (!defined('MYSQL_DATABASE')) {
    /** @psalm-suppress RiskyTruthyFalsyComparison */
    define('MYSQL_DATABASE', getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') : 'zmsbo');
}
// MYSQL_PORT of type "tcp://127.0.0.1:3306"
$mysqlPort = getenv('MYSQL_PORT');
if (is_string($mysqlPort) && $mysqlPort !== '') {
    $dsn = "mysql:dbname=" . MYSQL_DATABASE . ";host=";
    $dsn .= (string) parse_url($mysqlPort, PHP_URL_HOST);
    $dsn .= ';port=';
    $dsn .= (string) parse_url($mysqlPort, PHP_URL_PORT);
    if (!defined('DSN_RW')) {
        define('DSN_RW', $dsn);
    }
} else {
    if (!defined('DSN_RW')) {
        define('DSN_RW', 'mysql:dbname=' . MYSQL_DATABASE . ';host=127.0.0.1');
    }
}
// MYSQL_PORT_RO for readonly access of type "tcp://127.0.0.1:3306"
$mysqlPortRo = getenv('MYSQL_PORT_RO');
if (is_string($mysqlPortRo) && $mysqlPortRo !== '') {
    // Allow simple load balancing with multiple values
    $mysqlPortList = explode(',', $mysqlPortRo);
    $mysqlPortRO = trim($mysqlPortList[array_rand($mysqlPortList)]);
    $dsn = "mysql:dbname=" . MYSQL_DATABASE . ";host=";
    $dsn .= (string) parse_url($mysqlPortRO, PHP_URL_HOST);
    $dsn .= ';port=';
    $dsn .= (string) parse_url($mysqlPortRO, PHP_URL_PORT);
    if (!defined('DSN_RO')) {
        define('DSN_RO', $dsn);
    }
} else {
    if (!defined('DSN_RO')) {
        define('DSN_RO', DSN_RW);
    }
}

$value = getenv('ZMS_DLDB_TWIG_CACHE');
/** @psalm-suppress RiskyTruthyFalsyComparison */
define('ZMS_DLDB_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));

/**
 * @psalm-suppress InvalidConstantAssignmentValue
 */
class App extends \BO\Zmsbackend\Application
{
    const string APP_PATH = __DIR__;
    const string IDENTIFIER = 'Zmsbackend-ENV';
    const bool DEBUG = false;
    const bool DB_ENABLE_WSREPSYNCWAIT = true;
    /**
     * @var String DB_DSN_READONLY
     */
    const string DB_DSN_READONLY = DSN_RO;

    /**
     * @var String DB_DSN_READWRITE
     */
    const string DB_DSN_READWRITE = DSN_RW;

    /**
     * @var String DB_USERNAME
     */
    const string DB_USERNAME = MYSQL_USER;

    /**
     * @var String DB_PASSWORD
     */
    const string DB_PASSWORD = MYSQL_PASSWORD;

    /**
     * Use caching
     *
     */
    const string|false TWIG_CACHE = ZMS_DLDB_TWIG_CACHE;
    const string MODULE_NAME = 'zmsdldb';

    /** Fallback when settings key d115.openingTime is unset */
    const string D115_DEFAULT_OPENINGTIME = '';

    /** Fallback when settings key d115.messageHtml is unset */
    const string D115_DEFAULT_TEXT = '';

    /** Mapbox/OSM access token for frontend maps */
    const string OSM_ACCESS_TOKEN = '';

    /** Leaflet gestureHandling option value */
    const string OSM_GESTURE_HANDLING = 'true';
}

// Uncomment the following line for testing data with vendor/bin/importTestData
$timeAdjust = getenv('ZMS_TIMEADJUST');
if (is_string($timeAdjust) && $timeAdjust !== '') {
    App::$now = new DateTimeImmutable(date($timeAdjust), new DateTimeZone('Europe/Berlin'));
}

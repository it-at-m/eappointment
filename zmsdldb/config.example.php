<?php
// @codingStandardsIgnoreFile

// MYSQL_USER with access to DB
if (!defined('MYSQL_USER')) {
    define('MYSQL_USER', getenv('MYSQL_USER') ? getenv('MYSQL_USER') : 'root');
}
// MYSQL_PASSWORD
if (!defined('MYSQL_PASSWORD')) {
    define('MYSQL_PASSWORD', getenv('MYSQL_PASSWORD') ? getenv('MYSQL_PASSWORD') :'zmsbackend');
}
// MYSQL_DATABASE is the database name containing the tables
if (!defined('MYSQL_DATABASE')) {
    define('MYSQL_DATABASE', getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') : 'zmsbo');
}
// MYSQL_PORT of type "tcp://127.0.0.1:3306"
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
// MYSQL_PORT_RO for readonly access of type "tcp://127.0.0.1:3306"
if (getenv('MYSQL_PORT_RO')) {
    // Allow simple load balancing with multiple values
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

$value = getenv('ZMS_DLDB_TWIG_CACHE');
define('ZMS_DLDB_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));

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
    const TWIG_CACHE = ZMS_DLDB_TWIG_CACHE;
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
if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}

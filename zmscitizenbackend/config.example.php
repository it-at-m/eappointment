<?php
define('MAINTENANCE_MODE_ENABLED', filter_var(getenv('MAINTENANCE_ENABLED'), FILTER_VALIDATE_BOOLEAN));
define('ZMS_IDENTIFIER', getenv('ZMS_IDENTIFIER') ? getenv('ZMS_IDENTIFIER') : 'zms');
define('ZMS_MODULE_NAME', 'zmscitizenbackend');
define('ZMS_CITIZENLOGIN_EXTERNALUSERID_CLAIM_NAME', 'lhmExtID');

if (!defined('MYSQL_USER')) {
    define('MYSQL_USER', getenv('MYSQL_USER') ? getenv('MYSQL_USER') : 'root');
}
if (!defined('MYSQL_PASSWORD')) {
    define('MYSQL_PASSWORD', getenv('MYSQL_PASSWORD') ? getenv('MYSQL_PASSWORD') : 'zmsbackend');
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

class App extends \BO\Zmscitizenbackend\Application
{
    /**
     * Flag for enabling maintenance mode
     */
    const bool MAINTENANCE_MODE_ENABLED = MAINTENANCE_MODE_ENABLED;

    /**
     * Name of the application
     */
    const string IDENTIFIER = ZMS_IDENTIFIER;

    /**
     * Name of the module
     */
    const string MODULE_NAME = ZMS_MODULE_NAME;

    /**
     * Name of the OIDC claim that uniquely identifies a citizen user.
     */
    const string ZMS_CITIZENLOGIN_EXTERNALUSERID_CLAIM_NAME = ZMS_CITIZENLOGIN_EXTERNALUSERID_CLAIM_NAME;

    const string DB_DSN_READONLY = DSN_RO;
    const string DB_DSN_READWRITE = DSN_RW;
    const string DB_USERNAME = MYSQL_USER;
    const string DB_PASSWORD = MYSQL_PASSWORD;
}

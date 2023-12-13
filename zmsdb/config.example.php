<?php
// @codingStandardsIgnoreFile
//ONLY FOR TESTING

require(__DIR__ . '/vendor/autoload.php');
date_default_timezone_set('Europe/Berlin');

// MYSQL_DATABASE is the database name containing the tables
define('MYSQL_DATABASE', getenv('MYSQL_DATABASE') ?: 'zmsbo');

// Determine the host and port from the environment or use defaults
$host = getenv('MYSQL_HOST') ?: (getenv('MYSQL_PORT') ? parse_url(getenv('MYSQL_PORT'), PHP_URL_HOST) : '127.0.0.1');
$port = getenv('MYSQL_PORT') ? parse_url(getenv('MYSQL_PORT'), PHP_URL_PORT) : '3306';

// MYSQL_USER and MYSQL_PASSWORD
\BO\Zmsdb\Connection\Select::$username = getenv('MYSQL_USER') ?: 'root';
\BO\Zmsdb\Connection\Select::$password = getenv('MYSQL_ROOT_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: 'internet';

\BO\Zmsdb\Connection\Select::$enableProfiling = true;
\BO\Zmsdb\Connection\Select::$dbname_zms = MYSQL_DATABASE;
\BO\Zmsdb\Connection\Select::$readSourceName = "mysql:dbname=" . MYSQL_DATABASE . ";host=$host;port=$port";
\BO\Zmsdb\Connection\Select::$writeSourceName = "mysql:dbname=" . MYSQL_DATABASE . ";host=$host;port=$port";
\BO\Zmsdb\Connection\Select::$pdoOptions = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];

\BO\Zmsdb\Source\Dldb::$importPath = 'tests/Zmsdb/fixtures/';

if (getenv('ZMS_TIMEADJUST')) {
    class App {
        public static $now;
    }
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}

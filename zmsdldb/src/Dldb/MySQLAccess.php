<?php

/**
 * @package ClientDLDB
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
 *
 */
class MySQLAccess extends PDOAccess
{
    protected $engine = 'MySQL';

    const DEFAULT_DATABASE_NAME = 'dldb_frontend_dev';
    const DEFAULT_DATABASE_HOST = 'mariadb';
    const DEFAULT_DATABASE_PORT = 3306;
    const DEFAULT_DATABASE_USER = 'root';
    const DEFAULT_DATABASE_PASSWORD = 'password';

    protected function connect(array $options)
    {
        $host = $options['host'] ?? static::DEFAULT_DATABASE_HOST;
        $dbname = $options['database'] ?? static::DEFAULT_DATABASE_NAME;
        $user = $options['user'] ?? static::DEFAULT_DATABASE_USER;
        $pass = $options['password'] ?? static::DEFAULT_DATABASE_PASSWORD;
        $port = $options['port'] ?? static::DEFAULT_DATABASE_PORT;
        if (!$host) {
            $host = 'localhost';
        }

        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $dbname;

        try {
            $this->pdo = new \PDO($dsn, $user, $pass);
        } catch (\Exception $e) {
            if (stripos($e->getMessage(), 'SQLSTATE') !== false) {
                // Only sanitize actual connection errors
                if (
                    stripos($e->getMessage(), 'Connection refused') !== false ||
                    stripos($e->getMessage(), 'Connection timed out') !== false ||
                    stripos($e->getMessage(), 'Access denied') !== false
                ) {
                    $message = 'Database connection failed in zmsdldb/Dldb/MySQLAccess.php on line 45.';
                    throw new \Exception($message, (int)$e->getCode(), $e);
                }
            }
            throw $e;
        }
    }
}

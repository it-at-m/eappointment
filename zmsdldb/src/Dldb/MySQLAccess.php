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
            if (
                stripos($e->getMessage(), 'SQLSTATE') !== false &&
                (stripos($e->getMessage(), 'Connection refused') !== false ||
                 stripos($e->getMessage(), 'Connection timed out') !== false ||
                 stripos($e->getMessage(), 'Access denied') !== false)
            ) {
                $errorType = 'Connection refused';
                if (stripos($e->getMessage(), 'Connection timed out') !== false) {
                    $errorType = 'Connection timed out';
                } elseif (stripos($e->getMessage(), 'Access denied') !== false) {
                    $errorType = 'Access denied';
                }
                $message = 'Database connection failed (' . $errorType . ') in ' . $e->getFile() .  ' on line ' . $e->getLine() . '.';
                $exception = new \Exception($message, (int)$e->getCode(), $e);
                $reflection = new \ReflectionProperty('Exception', 'trace');
                $reflection->setAccessible(true);
                $reflection->setValue($exception, []);
                throw $exception;
            }
            throw $e;
        }
    }
}

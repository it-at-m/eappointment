<?php

/**
 * @package ClientDLDB
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
 *
 */
class SQLiteAccess extends PDOAccess
{
    const DEFAULT_DATABASE_NAME = 'dldb_frontend_dev';
    const DEFAULT_DATABASE_PATH = __DIR__;

    protected function connect(array $options)
    {
        try {
            $databasePath = rtrim(($options['databasePath'] ?? static::DEFAULT_DATABASE_PATH), \DIRECTORY_SEPARATOR);

            if (!is_dir($databasePath)) {
                mkdir($databasePath);
            }
            $database = ($options['database'] ?? static::DEFAULT_DATABASE_NAME) . '.db';
            $dsn = 'sqlite:' . $databasePath . \DIRECTORY_SEPARATOR . $database;

            $this->pdo = new \PDO($dsn);
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

    protected function postConnect()
    {
        try {
            $this->beginTransaction();
            $shemaQuerys = include(__DIR__ . '/shema/sqlite.php');

            foreach ($shemaQuerys as $query) {
                $this->exec($query);
            }
            $this->commit();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

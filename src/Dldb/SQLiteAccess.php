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

<?php

namespace BO\Zmsdb\Cli;

/**
 * @codeCoverageIgnore
 */
class Db
{
    public static $baseDSN = '';

    public static function startExecuteSqlFile($file, $dbname = null)
    {
        $pdo = self::startUsingDatabase($dbname);
        $startTime = microtime(true);
        //var_dump($pdo->fetchPairs('SHOW STATUS'));
        //var_dump($pdo->fetchAll('SHOW TABLES;'));
        $sqlFile = fopen($file, 'r');
        echo "Importing " . basename($file) . "\n";
        $query = '';
        while ($line = fgets($sqlFile)) {
            $query .= $line;
            if (false !== strpos($line, ';')) {
                try {
                    $pdo->exec($query);
                    echo ".";
                    //echo "Successful:\n$query\n";
                    $query = '';
                } catch (Exception $exception) {
                    echo "Offending query: \n$query\n";
                    throw $exception;
                }
            }
        }
        $time = round(microtime(true) - $startTime, 3);
        echo "\nTook $time seconds\n";
    }

    public static function startUsingDatabase($dbname = null)
    {
        if (!self::$baseDSN) {
            self::$baseDSN = \BO\Zmsdb\Connection\Select::$writeSourceName;
        }
        if ($dbname === null) {
            $dbname =& \BO\Zmsdb\Connection\Select::$dbname_zms;
        }
        \BO\Zmsdb\Connection\Select::closeWriteConnection();
        $dbname_zms =& \BO\Zmsdb\Connection\Select::$dbname_zms;
        \BO\Zmsdb\Connection\Select::$writeSourceName = str_replace("$dbname_zms", $dbname, self::$baseDSN);
        $pdo = \BO\Zmsdb\Connection\Select::getWriteConnection();
        return $pdo;
    }

    public static function startTestDataImport($fixtures)
    {
        $dbname_zms =& \BO\Zmsdb\Connection\Select::$dbname_zms;

        $pdo = self::startUsingDatabase('information_schema');
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname_zms`;");

        self::startExecuteSqlFile($fixtures . '/mysql_zmsbo.sql');
    }

    public static function startConfigDataImport()
    {
        $defaults = new \BO\Zmsentities\Config();
        (new \BO\Zmsdb\Config())->updateEntity($defaults);
    }

    public static function startMigrations($migrationList, $commit = true)
    {
        if (!is_array($migrationList)) {
            $migrationList = glob($migrationList . '/*.sql');
        }
        sort($migrationList);
        $pdo = self::startUsingDatabase();
        $migrationsDoneList = $pdo->fetchPairs('SELECT filename, changeTimestamp FROM migrations');
        $addedMigrations = 0;
        foreach ($migrationList as $migrationFile) {
            $migrationName = basename($migrationFile);
            if (!array_key_exists($migrationName, $migrationsDoneList)) {
                $addedMigrations++;
                if (!$commit) {
                    echo "$addedMigrations. Add migration $migrationName\n";
                } else {
                    self::startExecuteSqlFile($migrationFile);
                    $pdo->prepare('INSERT INTO `migrations` SET `filename` = :filename')
                        ->execute(['filename' => $migrationName]);
                }
            }
        }
        echo "\nFound " . count($migrationsDoneList) . " completed migrations and added $addedMigrations migrations.\n";
        return $addedMigrations;
    }
}

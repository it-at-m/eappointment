<?php

namespace BO\Zmsdb\Cli;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(Short)
 */
class Db
{
    public static $baseDSN = '';

    public static function startExecuteSqlFile($file, $dbname = null, $verbose = true)
    {
        $pdo = self::startUsingDatabase($dbname, $verbose);
        $startTime = microtime(true);

        // Check if file is compressed or not
        $isCompressed = (substr($file, -3) === '.gz');

        if ($isCompressed) {
            $sqlFile = gzopen($file, 'r');
            $readFunction = 'gzgets';
            $closeFunction = 'gzclose';
        } else {
            $sqlFile = fopen($file, 'r');
            $readFunction = 'fgets';
            $closeFunction = 'fclose';
        }

        if ($verbose) {
            \App::$log->info('Importing SQL file', ['file' => basename($file)]);
        }
        $query = '';
        while ($line = $readFunction($sqlFile)) {
            $query .= $line;
            if (preg_match('/;\s*$/', $line)) {
                try {
                    $pdo->exec($query);
                    //echo "Successful:\n$query\n";
                    $query = '';
                } catch (\Exception $exception) {
                    if ($verbose) {
                        \App::$log->error('SQL import failed', [
                            'file' => basename($file),
                            'method' => __METHOD__,
                            'exception' => get_class($exception),
                            'message' => $exception->getMessage(),
                            'code' => $exception->getCode(),
                        ]);
                    }
                    throw $exception;
                }
            }
        }
        $closeFunction($sqlFile);
        $time = round(microtime(true) - $startTime, 3);
        if ($verbose) {
            \App::$log->info('SQL import finished', [
                'file' => basename($file),
                'seconds' => $time,
            ]);
        }
    }

    public static function executeSql($query, $dbname = null)
    {
        $pdo = self::startUsingDatabase($dbname, false);
        $pdo->exec($query);
    }

    public static function startUsingDatabase($dbname = null, $verbose = true): \BO\Zmsdb\Connection\Pdo
    {
        if (!self::$baseDSN) {
            self::$baseDSN = \BO\Zmsdb\Connection\Select::$writeSourceName;
        }
        \BO\Zmsdb\Connection\Select::closeWriteConnection();
        if ($dbname === null) {
            \BO\Zmsdb\Connection\Select::$writeSourceName = self::$baseDSN;
        } else {
            $dbname_zms =& \BO\Zmsdb\Connection\Select::$dbname_zms;
            \BO\Zmsdb\Connection\Select::$writeSourceName =
                preg_replace("#dbname=$dbname_zms.*?;#", "dbname=$dbname;", self::$baseDSN);
        }

        if ($verbose) {
            \App::$log->info('Using database connection', [
                'dsn' => \BO\Zmsdb\Connection\Select::$writeSourceName,
            ]);
        }

        $pdo = \BO\Zmsdb\Connection\Select::getWriteConnection();
        return $pdo;
    }

    public static function startTestDataImport($fixtures, $filename = 'mysql_zmsbo.sql')
    {
        $dbname_zms =& \BO\Zmsdb\Connection\Select::$dbname_zms;

        $pdo = self::startUsingDatabase('information_schema');
        $pdo->exec("DROP DATABASE IF EXISTS `$dbname_zms`;");
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname_zms`;");

        self::startExecuteSqlFile($fixtures . '/' . $filename);
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
                    \App::$log->info('Pending migration', [
                        'index' => $addedMigrations,
                        'migration' => $migrationName,
                    ]);
                } else {
                    self::startExecuteSqlFile($migrationFile);
                    $pdo->prepare('INSERT INTO `migrations` SET `filename` = :filename')
                        ->execute(['filename' => $migrationName]);
                }
            }
        }
        \App::$log->info('Migration check finished', [
            'completed' => count($migrationsDoneList),
            'added' => $addedMigrations,
        ]);
        return $addedMigrations;
    }

    public static function executeTestData(string $testName, string $step)
    {
        $fixtures = realpath(__DIR__ . '/../../../tests/Zmsdb/fixtures/');
        $sqlFile = $fixtures . '/' . $testName . '/' . $step . '.sql';

        if (! file_exists($sqlFile)) {
            return;
        }

        self::startExecuteSqlFile(
            $sqlFile,
            null,
            false
        );
    }
}

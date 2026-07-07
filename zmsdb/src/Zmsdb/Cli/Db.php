<?php

namespace BO\Zmsdb\Cli;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(Short)
 */
class Db
{
    public static $baseDSN = '';

    public static function startExecuteSqlFile($file, $databaseName = null, $verbose = true)
    {
        $databaseConnection = self::startUsingDatabase($databaseName, $verbose);
        $startedAt = microtime(true);
        $sqlFileHandle = self::openSqlFileHandle($file);

        if ($verbose) {
            \App::$log->info('Importing SQL file', ['file' => basename($file)]);
        }

        $statementDelimiter = ';';
        $statementBuffer = '';

        while ($line = $sqlFileHandle['readLine']($sqlFileHandle['handle'])) {
            $delimiterFromLine = self::readDelimiterDirective($line);
            if ($delimiterFromLine !== null) {
                $statementDelimiter = $delimiterFromLine;
                continue;
            }

            $statementBuffer .= $line;
            if (!self::lineEndsWithStatementDelimiter($line, $statementDelimiter)) {
                continue;
            }

            $sqlStatement = self::extractSqlStatement($statementBuffer, $statementDelimiter);
            $statementBuffer = '';

            if ($sqlStatement === '') {
                continue;
            }

            self::executeSqlStatement($databaseConnection, $sqlStatement, $file, $verbose);
        }

        $sqlFileHandle['close']($sqlFileHandle['handle']);

        if ($verbose) {
            \App::$log->info('SQL import finished', [
                'file' => basename($file),
                'seconds' => round(microtime(true) - $startedAt, 3),
            ]);
        }
    }

    public static function executeSql($query, $databaseName = null)
    {
        $databaseConnection = self::startUsingDatabase($databaseName, false);
        $databaseConnection->exec($query);
    }

    public static function startUsingDatabase($databaseName = null, $verbose = true): \BO\Zmsdb\Connection\Pdo
    {
        if (!self::$baseDSN) {
            self::$baseDSN = \BO\Zmsdb\Connection\Select::$writeSourceName;
        }

        \BO\Zmsdb\Connection\Select::closeWriteConnection();
        self::applyDatabaseNameToConnection($databaseName);

        if ($verbose) {
            \App::$log->info('Using database connection', [
                'dsn' => \BO\Zmsdb\Connection\Select::$writeSourceName,
            ]);
        }

        return \BO\Zmsdb\Connection\Select::getWriteConnection();
    }

    public static function startTestDataImport($fixturesDirectory, $filename = 'mysql_zmsbo.sql')
    {
        $defaultDatabaseName =& \BO\Zmsdb\Connection\Select::$dbname_zms;

        $databaseConnection = self::startUsingDatabase('information_schema');
        $databaseConnection->exec("DROP DATABASE IF EXISTS `$defaultDatabaseName`;");
        $databaseConnection->exec("CREATE DATABASE IF NOT EXISTS `$defaultDatabaseName`;");

        self::startExecuteSqlFile($fixturesDirectory . '/' . $filename);
    }

    public static function startConfigDataImport()
    {
        $defaults = new \BO\Zmsentities\Config();
        (new \BO\Zmsdb\Config())->updateEntity($defaults);
    }

    public static function startMigrations($migrationList, $commit = true, ?string $phase = null)
    {
        $migrationFiles = self::resolveMigrationFileList($migrationList);
        $migrationFiles = self::filterMigrationFilesByPhase($migrationFiles, $phase);
        $databaseConnection = self::startUsingDatabase();
        $completedMigrations = $databaseConnection->fetchPairs(
            'SELECT filename, changeTimestamp FROM migrations'
        );
        $addedMigrationCount = 0;

        foreach ($migrationFiles as $migrationFile) {
            $migrationFilename = basename($migrationFile);
            if (array_key_exists($migrationFilename, $completedMigrations)) {
                continue;
            }

            $addedMigrationCount++;
            if (!$commit) {
                \App::$log->info('Pending migration', [
                    'index' => $addedMigrationCount,
                    'migration' => $migrationFilename,
                ]);
                continue;
            }

            self::startExecuteSqlFile($migrationFile);
            $databaseConnection->prepare('INSERT INTO `migrations` SET `filename` = :filename')
                ->execute(['filename' => $migrationFilename]);
        }

        \App::$log->info('Migration check finished', [
            'completed' => count($completedMigrations),
            'added' => $addedMigrationCount,
        ]);

        return $addedMigrationCount;
    }

    public static function executeTestData(string $testName, string $step)
    {
        $fixturesDirectory = realpath(__DIR__ . '/../../../tests/Zmsdb/fixtures/');
        $sqlFile = $fixturesDirectory . '/' . $testName . '/' . $step . '.sql';

        if (!file_exists($sqlFile)) {
            return;
        }

        self::startExecuteSqlFile($sqlFile, null, false);
    }

    private static function openSqlFileHandle(string $file): array
    {
        $isGzipCompressed = substr($file, -3) === '.gz';

        if ($isGzipCompressed) {
            return [
                'handle' => gzopen($file, 'r'),
                'readLine' => 'gzgets',
                'close' => 'gzclose',
            ];
        }

        return [
            'handle' => fopen($file, 'r'),
            'readLine' => 'fgets',
            'close' => 'fclose',
        ];
    }

    private static function readDelimiterDirective(string $line): ?string
    {
        if (!preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i', rtrim($line), $matches)) {
            return null;
        }

        return $matches[1];
    }

    private static function lineEndsWithStatementDelimiter(string $line, string $statementDelimiter): bool
    {
        if ($statementDelimiter === ';') {
            return (bool) preg_match('/;\s*$/', $line);
        }

        return (bool) preg_match(
            '/' . preg_quote($statementDelimiter, '/') . '\s*$/',
            rtrim($line)
        );
    }

    /**
     * Split migrations into expand/contract phases by filename for zero-downtime
     * (Expand–Contract) deployments.
     *
     * - contract phase: files whose name contains "-contract-" or "-contract." (destructive cleanup)
     * - expand phase:   every other file, i.e. "*-expand-*" plus unprefixed additive migrations
     * - no phase (null/empty): all files, preserving the legacy behaviour
     */
    private static function filterMigrationFilesByPhase(array $migrationFiles, ?string $phase): array
    {
        if ($phase === null || $phase === '') {
            return $migrationFiles;
        }

        $isContract = static function (string $migrationFile): bool {
            return (bool) preg_match('/-contract[-.]/', basename($migrationFile));
        };

        if ($phase === 'contract') {
            return array_values(array_filter($migrationFiles, $isContract));
        }

        if ($phase === 'expand') {
            return array_values(array_filter(
                $migrationFiles,
                static fn(string $migrationFile): bool => !$isContract($migrationFile)
            ));
        }

        throw new \InvalidArgumentException(
            "Unknown migration phase '$phase'; expected 'expand' or 'contract'"
        );
    }

    private static function extractSqlStatement(string $statementBuffer, string $statementDelimiter): string
    {
        $sqlStatement = $statementBuffer;

        if ($statementDelimiter !== ';') {
            $sqlStatement = preg_replace(
                '/' . preg_quote($statementDelimiter, '/') . '\s*$/',
                '',
                $sqlStatement
            );
        }

        return trim($sqlStatement);
    }

    private static function executeSqlStatement(
        \BO\Zmsdb\Connection\Pdo $databaseConnection,
        string $sqlStatement,
        string $sourceFile,
        bool $verbose
    ): void {
        try {
            $databaseConnection->exec($sqlStatement);
        } catch (\Exception $exception) {
            if ($verbose) {
                \App::$log->error('SQL import failed', [
                    'file' => basename($sourceFile),
                    'method' => __METHOD__,
                    'exception' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                ]);
            }

            throw $exception;
        }
    }

    private static function applyDatabaseNameToConnection(?string $databaseName): void
    {
        if ($databaseName === null) {
            \BO\Zmsdb\Connection\Select::$writeSourceName = self::$baseDSN;
            return;
        }

        $defaultDatabaseName =& \BO\Zmsdb\Connection\Select::$dbname_zms;
        \BO\Zmsdb\Connection\Select::$writeSourceName = preg_replace(
            "#dbname=$defaultDatabaseName.*?;#",
            "dbname=$databaseName;",
            self::$baseDSN
        );
    }

    private static function resolveMigrationFileList($migrationList): array
    {
        if (!is_array($migrationList)) {
            $migrationList = glob($migrationList . '/*.sql');
        }

        sort($migrationList);

        return $migrationList;
    }
}

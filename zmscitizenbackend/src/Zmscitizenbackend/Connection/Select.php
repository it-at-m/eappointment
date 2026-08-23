<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Connection;

use BO\Slim\Helper\Sanitizer;
use BO\Zmscitizenbackend\Connection\Exception\PDOFailed;

/**
 * @codeCoverageIgnore
 *
 * @SuppressWarnings(TooManyFields)
 * Handle read and write connections
 */
class Select
{
    public static bool $enableProfiling = false;

    public static ?string $readSourceName = null;

    public static ?string $writeSourceName = null;

    public static string $dbname_zms = 'zmsbo';

    public static ?string $username = null;

    public static ?string $password = null;

    public static array $pdoOptions = [];

    public static string $connectionTimezone = ' UTC';

    public static bool $enableWsrepSyncWait = false;

    public static bool $galeraConnection = false;

    protected static ?Pdo $readConnection = null;

    protected static ?Pdo $writeConnection = null;

    protected static mixed $readProfiler = null;

    protected static mixed $writeProfiler = null;

    protected static bool $useTransaction = false;

    protected static bool $useProfiling = false;

    protected static bool $useQueryCache = true;

    protected static function sanitizeStackTrace(string $trace): string
    {
        return Sanitizer::sanitizeStackTrace($trace);
    }

    protected static function createPdoConnection(string $dataSourceName): Pdo
    {
        try {
            $pdoOptions = array_merge([], self::$pdoOptions);
            $pdo = new Pdo($dataSourceName, self::$username, self::$password, $pdoOptions);
            $pdo->exec('SET NAMES "UTF8";');
            $pdo->exec('SET SESSION sql_mode = "STRICT_ALL_TABLES";');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $exception) {
            $sanitizedDsn = self::sanitizeStackTrace($dataSourceName);
            $sanitizedMessage = self::sanitizeStackTrace($exception->getMessage());
            throw new PDOFailed($sanitizedDsn . ': ' . $sanitizedMessage);
        }
        return $pdo;
    }

    public static function setReadConnection(PdoInterface $connection): void
    {
        if (!$connection instanceof Pdo) {
            throw new \InvalidArgumentException('Expected ' . Pdo::class);
        }
        self::$readConnection = $connection;
    }

    public static function getReadConnection(): Pdo
    {
        if (null === self::$readConnection) {
            self::$readConnection = self::createPdoConnection(self::$readSourceName);
            self::$readProfiler = new \Aura\Sql\Profiler\Profiler();
            self::$readProfiler->setActive(self::$enableProfiling);
            self::$readConnection->setProfiler(self::$readProfiler);
            if (!self::$useQueryCache) {
                try {
                    self::$readConnection->exec('SET SESSION query_cache_type = 0;');
                } catch (\Exception $exception) {
                    // ignore, query cache might be disabled
                }
            }
            if (self::$useProfiling) {
                self::$readConnection->exec('SET profiling = 1;');
            }
        }
        return self::$readConnection;
    }

    public static function hasReadConnection(): bool
    {
        return null !== self::$readConnection;
    }

    public static function closeReadConnection(): void
    {
        self::$readConnection = null;
    }

    public static function setWriteConnection(PdoInterface $connection): void
    {
        if (!$connection instanceof Pdo) {
            throw new \InvalidArgumentException('Expected ' . Pdo::class);
        }
        self::$writeConnection = $connection;
    }

    public static function getWriteConnection(): Pdo
    {
        if (null === self::$writeConnection) {
            self::$writeConnection = self::createPdoConnection(self::$writeSourceName);
            self::$writeProfiler = new \Aura\Sql\Profiler\Profiler();
            self::$writeProfiler->setActive(self::$enableProfiling);
            self::$writeConnection->setProfiler(self::$writeProfiler);
            if (self::$useTransaction) {
                self::$writeConnection->exec('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
                self::$writeConnection->exec('SET SESSION innodb_lock_wait_timeout=15');
                self::$writeConnection->beginTransaction();
            }
            if (!self::$useQueryCache) {
                try {
                    self::$writeConnection->exec('SET SESSION query_cache_type = 0;');
                } catch (\Exception $exception) {
                    // ignore, query cache might be disabled
                }
            }
            if (self::$useProfiling) {
                self::$writeConnection->exec('SET profiling = 1;');
            }
            if (self::$galeraConnection && self::$enableWsrepSyncWait) {
                self::$writeConnection->exec(
                    'SET SESSION wsrep_sync_wait = (
                        SELECT CAST(value AS INT) FROM config WHERE name = "setting__wsrepsync"
                    );'
                );
            }
            self::$readConnection = self::$writeConnection;
        }
        return self::$writeConnection;
    }

    public static function hasWriteConnection(): bool
    {
        return null !== self::$writeConnection;
    }

    public static function closeWriteConnection(): void
    {
        self::$writeConnection = null;
    }

    public static function setQueryCache(bool $useQueryCache = true): void
    {
        static::$useQueryCache = $useQueryCache;
    }

    public static function setProfiling(bool $useProfiling = true): void
    {
        static::$useProfiling = $useProfiling;
    }

    public static function setCriticalReadSession(bool $wsrepStatus = true): void
    {
        static::$enableWsrepSyncWait = $wsrepStatus;
        static::getWriteConnection();
    }

    public static function setTransaction(bool $useTransaction = true): void
    {
        static::$useTransaction = $useTransaction;
    }

    public static function writeRollback(): bool|null
    {
        if (self::$useTransaction && self::getWriteConnection()->inTransaction()) {
            return self::getWriteConnection()->rollBack();
        }
        return null;
    }

    public static function writeCommit(): bool|null
    {
        if (self::$useTransaction && null !== self::$writeConnection && self::getWriteConnection()->inTransaction()) {
            $status = self::getWriteConnection()->commit();
            self::$writeConnection->beginTransaction();
            return $status;
        }
        return null;
    }
}

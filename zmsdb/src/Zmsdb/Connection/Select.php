<?php

namespace BO\Zmsdb\Connection;

/**
 *
 * @codeCoverageIgnore
 *
 * @SuppressWarnings(TooManyFields)
 * Handle read and write connections
 */
class Select
{
    /**
     * @var Bool $enableProfiling
     */
    public static $enableProfiling = false;

    /**
     * @var String $readSourceName PDO connection string
     */
    public static $readSourceName = null;

    /**
     * @var String $writeSourceName PDO connection string
     */
    public static $writeSourceName = null;

    /**
     * @var String $dbname_zms
     */
    public static $dbname_zms = 'zmsbo';

    /**
     * @var String $username Login
     */
    public static $username = null;

    /**
     * @var String $password Credential
     */
    public static $password = null;

    /**
     * @var Array $pdoOptions compatible to the 4th PDO::__construct parameter
     */
    public static $pdoOptions = [];

    /**
     * @var String $connectionTimezone
     *
     */
    public static $connectionTimezone = ' UTC';

    /**
     * @var Bool $enableWsrepSyncWait
     */
    public static $enableWsrepSyncWait = false;

    /**
     * @var Bool $enableWsrepSyncWait
     */
    public static $galeraConnection = false;

    /**
     * @var PdoInterface $readConnection for read only requests
     */
    protected static $readConnection = null;

    /**
     * @var PdoInterface $writeConnection for write only requests
     */
    protected static $writeConnection = null;

    /**
     * @var \Aura\Sql\Profiler $readProfiler for read only requests
     */
    protected static $readProfiler = null;

    /**
     * @var \Aura\Sql\Profiler $writeProfiler for write only requests
     */
    protected static $writeProfiler = null;

    /**
     * @var Bool $useTransaction
     *
     */
    protected static $useTransaction = false;

    /**
     * @var Bool $useProfiling
     *
     */
    protected static $useProfiling = false;

    /**
     * @var Bool $useQueryCache
     *
     */
    protected static $useQueryCache = true;

    /**
     * Create a PDO compatible object
     *
     * @param  String $dataSourceName compatible with PDO
     * @return PdoInterface
     */
    protected static function createPdoConnection($dataSourceName)
    {
        try {
            $pdoOptions = array_merge([
                Pdo::ATTR_TIMEOUT => 6000, // Set the timeout to 30 seconds
            ], self::$pdoOptions);
            $pdo = new Pdo($dataSourceName, self::$username, self::$password, $pdoOptions);
            $pdo->exec('SET NAMES "UTF8";');
            //$timezone = date_default_timezone_get();
            //$pdo->prepare('SET time_zone = ?;')->execute([$timezone]);
            $pdo->exec('SET SESSION sql_mode = "STRICT_ALL_TABLES";');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $exception) {
            // Extend exception message with connection information
            $connectInfo = $dataSourceName;
            throw new \BO\Zmsdb\Exception\Pdo\PDOFailed(
                $connectInfo . $exception->getMessage(),
                (int)$exception->getCode(),
                $exception
            );
        }
        return $pdo;
    }


    /**
     * Set the read connection.
     * Usually this function is only required to set mockups for testing
     *
     * @param PdoInterface $connection
     */
    public static function setReadConnection(PdoInterface $connection)
    {
        self::$readConnection = $connection;
    }

    /**
     * Create or return a connection for reading data
     *
     * @return PdoInterface
     */
    public static function getReadConnection()
    {
        if (null === self::$readConnection) {
            self::$readConnection = self::createPdoConnection(self::$readSourceName);
            self::$readProfiler = new \Aura\Sql\Profiler\Profiler();
            self::$readProfiler->setActive(self::$enableProfiling);
            self::$readConnection->setProfiler(self::$readProfiler);
            //self::$readConnection->exec('SET SESSION TRANSACTION READ ONLY');
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

    /**
     * Test if a read connection is established
     *
     */
    public static function hasReadConnection()
    {
        return (null === self::$readConnection) ? false : true;
    }

    /**
     * Close a connection for reading data
     *
     */
    public static function closeReadConnection()
    {
        self::$readConnection = null;
    }

    /**
     * Set the write connection.
     * Usually this function is only required to set mockups for testing
     *
     * @param  PdoInterface $connection
     * @return self
     */
    public static function setWriteConnection(PdoInterface $connection)
    {
        self::$writeConnection = $connection;
    }

    /**
     * Create or return a connection for writing data
     *
     * @return PdoInterface
     */
    public static function getWriteConnection()
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
            // On writing, use the same host to avoid racing/transaction conditions
            self::$readConnection = self::$writeConnection;
        }
        return self::$writeConnection;
    }

    /**
     * Test if a write connection is established
     *
     */
    public static function hasWriteConnection()
    {
        return (null === self::$writeConnection) ? false : true;
    }

    /**
     * Close a connection for writing data
     *
     */
    public static function closeWriteConnection()
    {
        self::$writeConnection = null;
    }

    /**
     * Set query cache
     *
     * @param Bool $useQueryCache
     *
     */
    public static function setQueryCache($useQueryCache = true)
    {
        static::$useQueryCache = $useQueryCache;
    }

    /**
     * Set profiling
     *
     * @param Bool $useProfiling
     *
     */
    public static function setProfiling($useProfiling = true)
    {
        static::$useProfiling = $useProfiling;
    }

    /**
     * Set cluster wide causality checks, needed for critical reads across different nodes
     * @param Bool $wsrepStatus Set to true for critical reads
     */
    public static function setCriticalReadSession($wsrepStatus = true)
    {
        static::$enableWsrepSyncWait = $wsrepStatus;
        static::getWriteConnection();
    }

    /**
     * Set transaction
     *
     * @param Bool $useTransaction
     *
     */
    public static function setTransaction($useTransaction = true)
    {
        static::$useTransaction = $useTransaction;
    }

    /**
     * Rollback transaction if started
     *
     */
    public static function writeRollback()
    {
        if (self::$useTransaction && self::getWriteConnection()->inTransaction()) {
            return self::getWriteConnection()->rollBack();
        }
        return null;
    }

    /**
     * Commit transaction if started
     *
     */
    public static function writeCommit()
    {
        if (self::$useTransaction && null !== self::$writeConnection && self::getWriteConnection()->inTransaction()) {
            $status = self::getWriteConnection()->commit();
            self::$writeConnection->beginTransaction();
            return $status;
        }
        return null;
    }
}

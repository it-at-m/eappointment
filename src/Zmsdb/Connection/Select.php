<?php

namespace BO\Zmsdb\Connection;

/**
 *
 * @codeCoverageIgnore
 *
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
     * @var String $dbname_dldb
     */
    public static $dbname_dldb = 'startinfo';

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
     * Create a PDO compatible object
     *
     * @param  String $dataSourceName compatible with PDO
     * @return PdoInterface
     */
    protected static function createPdoConnection($dataSourceName)
    {
        try {
            $pdoOptions = array_merge([
                ], self::$pdoOptions);
            $pdo = new Pdo($dataSourceName, self::$username, self::$password, $pdoOptions);
            $pdo->exec('SET NAMES "UTF8";');
            //$timezone = date_default_timezone_get();
            //$pdo->prepare('SET time_zone = ?;')->execute([$timezone]);
            $pdo->exec('SET SESSION sql_mode = "STRICT_ALL_TABLES";');
        } catch (\Exception $exception) {
            // Extend exception message with connection information
            $connectInfo = $dataSourceName;
            throw new \Exception($connectInfo . $exception->getMessage(), (int)$exception->getCode(), $exception);
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
            self::$readProfiler = new \Aura\Sql\Profiler();
            self::$readProfiler->setActive(self::$enableProfiling);
            self::$readConnection->setProfiler(self::$readProfiler);
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
            self::$writeProfiler = new \Aura\Sql\Profiler();
            self::$writeProfiler->setActive(self::$enableProfiling);
            self::$writeConnection->setProfiler(self::$writeProfiler);
            if (self::$useTransaction) {
                self::$writeConnection->beginTransaction();
            }
            // On writing, use the same host to avoid racing/transcation conditions
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
        if (self::$useTransaction && self::getWriteConnection()->inTransaction()) {
            return self::getWriteConnection()->commit();
        }
        return null;
    }
}

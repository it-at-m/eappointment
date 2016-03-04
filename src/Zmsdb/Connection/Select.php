<?php

namespace BO\Zmsdb\Connection;

/**
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
     * Create a PDO compatible object
     *
     * @param  String $dataSourceName compatible with PDO
     * @return PdoInterface
     */
    protected static function createPdoConnection($dataSourceName)
    {
        $pdo = new Pdo($dataSourceName, self::$username, self::$password, self::$pdoOptions);
        $pdo->exec('SET NAMES "UTF8";');
        return $pdo;
    }

    /**
     * Enable profiling for sql queries
     */
    public static function disableProfilers()
    {
        self::$readProfiler->setActive(false);
        self::$write->setActive(false);
    }

    /**
     * Enable profiling for sql queries
     */
    public static function enableProfilers()
    {
        self::$readProfiler->setActive(true);
        self::$write->setActive(true);
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
        }
        return self::$writeConnection;
    }
}

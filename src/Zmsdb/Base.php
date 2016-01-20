<?php

namespace BO\Zmsdb;

abstract class Base
{
    /**
     * @var \PDO $writeDb Database connection
     */
    protected $writeDb = null;

    /**
     * @var \PDO $readDb Database connection
     */
    protected $readDb = null;

    /**
     * @param \PDO $writeConnection
     * @param \PDO $readConnection
     */
    public function __construct(\PDO $writeConnection = null, \PDO $readConnection = null)
    {
        if (null === $writeConnection) {
            $writeConnection = Connection\Select::getWriteConnection();
        }
        if (null === $readConnection) {
            $readConnection = Connection\Select::getReadConnection();
        }
        $this->writeDb = $writeConnection;
        $this->readDb = $readConnection;
    }

    /**
     * @return \PDO
     */
    public function getWriter()
    {
        return $this->writeDb;
    }

    /**
     * @return \PDO
     */
    public function getReader()
    {
        return $this->readDb;
    }
}

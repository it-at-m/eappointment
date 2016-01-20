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

    public function __construct($writeConnection = null, $readConnection = null)
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

    public function getWriter()
    {
        return $this->writeDb;
    }

    public function getReader()
    {
        return $this->readDb;
    }
}

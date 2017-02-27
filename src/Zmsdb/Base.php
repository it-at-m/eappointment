<?php

namespace BO\Zmsdb;

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */

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
     * Cache prepared statements
     *
     */
    protected static $preparedStatements = [];

    /**
     * Make sure, we do not cache statements on different connections
     *
     */
    protected static $preparedConnectionId = null;

    /**
     * @param \PDO $writeConnection
     * @param \PDO $readConnection
     */
    public function __construct(\PDO $writeConnection = null, \PDO $readConnection = null)
    {
        $this->writeDb = $writeConnection;
        $this->readDb = $readConnection;
    }

    /**
     * @return \PDO
     */
    public function getWriter()
    {
        if (null === $this->writeDb) {
            $this->writeDb = Connection\Select::getWriteConnection();
            $this->readDb = $this->writeDb;
        }
        return $this->writeDb;
    }

    /**
     * @return \PDO
     */
    public function getReader()
    {
        if (null === $this->readDb) {
            $this->readDb= Connection\Select::getReadConnection();
        }
        return $this->readDb;
    }

    public function fetchPreparedStatement(Query\Base $query)
    {
        $sql = "$query";
        $reader = $this->getReader();
        if (spl_object_hash($reader) != static::$preparedConnectionId) {
            // do not use prepared statements on a different connection
            static::$preparedStatements = [];
            static::$preparedConnectionId = spl_object_hash($reader);
        }
        if (!isset(static::$preparedStatements[$sql])) {
            $prepared = $this->getReader()->prepare($sql);
            static::$preparedStatements[$sql] = $prepared;
        }
        return static::$preparedStatements[$sql];
    }

    public function startExecute($statement, $parameters)
    {
        try {
            $statement->execute($parameters);
        } catch (\PDOException $pdoException) {
            $message = "SQL: "
                . " Err: "
                .$pdoException->getMessage()
                . " || Statement: "
                .$statement->queryString
                ." || Parameters=". var_export($parameters, true);
            throw new Exception\PDOFailed($message, 0, $pdoException);
        }
        return $statement;
    }

    public function fetchStatement(Query\Base $query)
    {
        $parameters = $query->getParameters();
        $statement = $this->startExecute($this->fetchPreparedStatement($query), $parameters);
        return $statement;
    }

    public function fetchOne(Query\Base $query, \BO\Zmsentities\Schema\Entity $entity)
    {
        $statement = $this->fetchStatement($query);
        $data = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            $entity->exchangeArray($query->postProcess($data));
        }/* else {
            throw new Exception\PDOFailed("Could not fetch one: ". $query->getName()
                . " --> " . var_export($query->getParameters(), 1));
        }*/
        return $entity;
    }

    public function fetchList(Query\Base $query, \BO\Zmsentities\Schema\Entity $entity)
    {
        $resultList = [];
        $statement = $this->fetchStatement($query);
        while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $dataEntity = clone $entity;
            $dataEntity->exchangeArray($query->postProcess($data));
            $resultList[] = $dataEntity;
        }
        return $resultList;
    }

    /**
     * Write an Item to database - Insert, Update
     *
     * @return \PDO
     */
    public function writeItem(Query\Base $query)
    {
        $statement = $this->getWriter()->prepare($query->getSql());
        return $statement->execute($query->getParameters());
    }

    public function deleteItem(Query\Base $query)
    {
        $statement = $this->getWriter()->prepare($query->getSql());
        return $statement->execute($query->getParameters());
    }
}

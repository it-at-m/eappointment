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

    public function fetchOne(Query\Base $query, \BO\Zmsentities\Schema\Entity $entity)
    {
        $query->addLimit(1);
        $sql = $query->getSql();
        $parameters = $query->getParameters();
        try {
            $data = $this->getReader()->fetchOne($query->getSql(), $query->getParameters());
        } catch (\PDOException $pdoException) {
            $message = "SQL: $sql || Parameters=". var_export($parameters, true);
            throw new Exception\PDOFailed($message, 0, $pdoException);
        }
        if ($data) {
            $entity->exchangeArray($query->postProcess($data));
        }
        return $entity;
    }

    public function fetchList(Query\Base $query, \BO\Zmsentities\Schema\Entity $entity)
    {
        $resultList = [];
        $sql = $query->getSql();
        $parameters = $query->getParameters();
        try {
            $dataList = $this->getReader()->fetchAll($sql, $parameters);
        } catch (\PDOException $pdoException) {
            $message = "SQL: $sql || Parameters=". var_export($parameters, true);
            throw new Exception\PDOFailed($message, 0, $pdoException);
        }
        foreach ($dataList as $data) {
            $dataEntity = clone $entity;
            $dataEntity->exchangeArray($query->postProcess($data));
            $resultList[] = $dataEntity;
        }
        return $resultList;
    }

    public function fetchStatement(Query\Base $query)
    {
        $sql = $query->getSql();
        $parameters = $query->getParameters();
        try {
            $statement = $this->getReader()->prepare($sql);
            $statement->execute($parameters);
        } catch (\PDOException $pdoException) {
            $message = "SQL: $sql || Parameters=". var_export($parameters, true);
            throw new Exception\PDOFailed($message, 0, $pdoException);
        }
        return $statement;
    }

    /**
     * Write an Item to database - Insert, Update
     * TODO: Check if there is a smarter way to do mapping with entity
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

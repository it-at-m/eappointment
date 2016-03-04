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

    public function fetchOne(Query\Base $query, \BO\Zmsentities\Schema\Entity $entity)
    {
        $query->addLimit(1);
        $data = $this->getReader()->fetchOne($query->getSql(), $query->getParameters());
        if ($data) {
            $entity->exchangeArray($data);
        }
        return $entity;
    }

    public function fetchList(Query\Base $query, \BO\Zmsentities\Schema\Entity $entity)
    {
        $resultList = [];
        $dataList = $this->getReader()->fetchAll($query->getSql(), $query->getParameters());
        foreach ($dataList as $data) {
            $dataEntity = clone $entity;
            $dataEntity->exchangeArray($data);
            $resultList[] = $dataEntity;
        }
        return $resultList;
    }

    /**
     * Write an Item to database - Insert, Update
     * TODO: Check if there is a smarter way to do mapping with entity
     *
     * @return \PDO
     */
    public function writeItem(Query\Base $query, $table = '', $replace = '')
    {
        //\App::$log->debug('params', [$query->getParameters()]);
        $statement = $this->getWriter()->prepare(str_replace($table, $replace, $query->getSql()));
        $statement->execute($query->getParameters());
    }
}

<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Session as Entity;

class Session extends Base
{
    /**
     * Fetch status from db
     *
     * @return \BO\Zmsentities\Session
     */
    public function readEntity($sessionName, $sessionId)
    {
        $query = new Query\Session(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionSessionId($sessionId)
            ->addConditionSessionName($sessionName);
        $session = $this->fetchOne($query, new Entity());
        if ($session && ! $session->hasId()) {
            return null;
        }
        return $session;
    }

    public function updateEntity($session)
    {
        $query = Query\Session::QUERY_WRITE;
        $statement = $this->getWriter()->prepare($query);
        $statement->execute(
            array(
            $session['id'],
            $session['name'],
            $session['content']
            )
        );
        $entity = $this->readEntity($session['name'], $session['id']);
        return $entity;
    }

    public function deleteEntity($sessionName, $sessionId)
    {
        $query = Query\Session::QUERY_DELETE;
        $statement = $this->getWriter()->prepare($query);
        $result = $statement->execute(
            array(
            $sessionId,
            $sessionName
            )
        );
        return ($result) ? true : false;
    }

    public function deleteByTimeInterval($sessionName, $deleteInSeconds)
    {
        $selectQuery = new Query\Session(Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addConditionSessionName($sessionName)
            ->addConditionSessionDeleteInterval($deleteInSeconds);
        $statement = $this->fetchStatement($selectQuery);
        while ($sessionData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($sessionData);
            if ($entity instanceof Entity) {
                $deleteQuery = new Query\Session(Query\Base::DELETE);
                $deleteQuery
                    ->addConditionSessionName($sessionName)
                    ->addConditionSessionId($entity->id);
                $this->deleteItem($deleteQuery);
            }
        }
    }
}

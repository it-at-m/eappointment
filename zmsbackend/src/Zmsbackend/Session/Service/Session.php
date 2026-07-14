<?php

namespace BO\Zmsbackend\Session\Service;

use BO\Zmsentities\Session as Entity;

class Session extends \BO\Zmsbackend\Base
{
    /**
     * Fetch status from db
     *
     * @return \BO\Zmsentities\Session
     */
    public function readEntity($sessionName, $sessionId)
    {
        $query = new \BO\Zmsbackend\Session\Repository\Session(\BO\Zmsbackend\Query\Base::SELECT);
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
        $query = \BO\Zmsbackend\Session\Repository\Session::QUERY_WRITE;
        $this->perform($query, array(
            $session['id'],
            $session['name'],
            json_encode($session['content'])
        ));
        $entity = $this->readEntity($session['name'], $session['id']);
        return $entity;
    }

    public function deleteEntity($sessionName, $sessionId)
    {
        $query = \BO\Zmsbackend\Session\Repository\Session::QUERY_DELETE;
        $result = $this->perform($query, array(
            $sessionId,
            $sessionName
        ));
        return ($result) ? true : false;
    }

    public function deleteByTimeInterval($sessionName, $deleteInSeconds)
    {
        $selectQuery = new \BO\Zmsbackend\Session\Repository\Session(\BO\Zmsbackend\Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addConditionSessionName($sessionName)
            ->addConditionSessionDeleteInterval($deleteInSeconds);
        $statement = $this->fetchStatement($selectQuery);
        while ($sessionData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $sessionData = (new \BO\Zmsbackend\Session\Repository\Session(\BO\Zmsbackend\Query\Base::SELECT))->postProcessJoins($sessionData);
            $entity = new Entity($sessionData);
            $deleteQuery = new \BO\Zmsbackend\Session\Repository\Session(\BO\Zmsbackend\Query\Base::DELETE);
            $deleteQuery
                ->addConditionSessionName($sessionName)
                ->addConditionSessionId($entity->id);
            $this->deleteItem($deleteQuery);
        }
    }
}

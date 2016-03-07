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
        return $this->fetchOne($query, new Entity());
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
        $statement->execute(
            array(
            $sessionId,
            $sessionName
            )
        );
    }
}

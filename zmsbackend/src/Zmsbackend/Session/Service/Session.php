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
    public function readEntity(string $sessionName, string|int $sessionId)
    {
        $query = new \BO\Zmsbackend\Session\Repository\Session(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionSessionId($sessionId)
            ->addConditionSessionName($sessionName);
        $session = $this->fetchOne($query, new Entity());
        if (!$session->hasId()) {
            return null;
        }
        return $session;
    }

    public function updateEntity(Entity $session): Entity
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

    public function deleteEntity(string $sessionName, string|int $sessionId): bool
    {
        $query = \BO\Zmsbackend\Session\Repository\Session::QUERY_DELETE;
        $result = $this->perform($query, array(
            $sessionId,
            $sessionName
        ));
        return ($result) ? true : false;
    }

    public function deleteByTimeInterval(?string $sessionName, int $deleteInSeconds): void
    {
        if ($sessionName !== null && $sessionName !== '') {
            $this->perform(\BO\Zmsbackend\Session\Repository\Session::QUERY_DELETE_BY_AGE_AND_NAME, [
                $deleteInSeconds,
                $sessionName,
            ]);
            return;
        }
        $this->perform(\BO\Zmsbackend\Session\Repository\Session::QUERY_DELETE_BY_AGE, [
            $deleteInSeconds,
        ]);
    }
}

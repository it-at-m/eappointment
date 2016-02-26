<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Session as Entity;

class Session extends Base
{
    /**
     * Fetch status from db
     * @return \BO\Zmsentities\Session
     */
    public function readEntity($sessionName, $sessionId)
    {
        $entity = new Entity();
        $sessionContent = $this->readSessionData($sessionName, $sessionId);
        $entity['name'] = $sessionName;
        $entity['id'] = $sessionId;
        $entity['content'] = $sessionContent;
        return $entity;
    }

    public function updateEntity($session)
    {
        $this->writeSessionData($session['name'], $session['id'], $session['content']);
        $entity = $this->readEntity($session['name'], $session['id']);
        return $entity;
    }

    public function deleteEntity($sessionName, $sessionId)
    {
        $this->deleteSessionData($sessionName, $sessionId);
    }

    protected function readSessionData($sessionName, $sessionId)
    {
        $result = $this->getReader()->fetchOne('
            SELECT
                sessioncontent
            FROM
                sessiondata
            WHERE
                sessionid = ? AND
                sessionname = ?
            ', array(
                    $sessionId,
                    $sessionName
                ));
        return $result['sessioncontent'];
    }

    protected function writeSessionData($sessionName, $sessionId, $sessionContent)
    {
        $query = '
            REPLACE INTO
                sessiondata
            SET
                sessionid=?,
                sessionname=?,
                sessioncontent=?
        ';
        $statement = $this->getWriter()->prepare($query);
        $statement->execute(array(
            $sessionId,
            $sessionName,
            $sessionContent
        ));
    }

    protected function deleteSessionData($sessionName, $sessionId)
    {
        $query = '
            DELETE FROM
                sessiondata
            WHERE
                sessionid=? AND
                sessionname=?
        ';
        $statement = $this->getWriter()->prepare($query);
        $statement->execute(array(
            $sessionId,
            $sessionName
        ));
    }
}

<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Session as Entity;

class Session extends Base
{
    /**
     * Fetch status from db
     * @return \BO\Zmsentities\Status
     */
    public function readEntity()
    {
        $entity = new Entity();
        $entity['name'] = session_name();
        $entity['id'] = session_id();
        $entity['content'] = $_SESSION;
        return $entity;
    }
}

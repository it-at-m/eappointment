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
        $handler = new Helper\ZMS_SessionHandler();
        session_set_save_handler($handler, true);        
        session_name($handler->sessionName);
        session_start();
        $entity['name'] = $handler->sessionName;
        $entity['id'] = session_id();
        $entity['content'] = $_SESSION;
        return $entity;
    }
}

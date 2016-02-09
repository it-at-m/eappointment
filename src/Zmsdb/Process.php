<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Process as Entity;

class Process extends Base
{
    public function readEntity($processId, $authKey, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProcessId($processId)
            ->addConditionAuthKey($authKey);
        //var_dump($query->getSql());
        //var_dump($this->fetchOne($query, new Entity()));
        return $this->fetchOne($query, new Entity());
    }
}

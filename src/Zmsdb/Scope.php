<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Scope as Entity;

class Scope extends Base
{
    public function readEntity($scopeId)
    {
        $entity = null;
        $query = new Query\Scope(Query\Base::SELECT);
        $query->setEntityMapping();
        $query->select('*');
        $query->from('standort', 'scope')
            ->where('StandortID', '=', $scopeId)
            ;
        var_dump((string)$query);
        $data = $this->getReader()->fetchOne($query->getSql(), $query->getParameters());
        if ($data) {
            $entity = new Entity($data);
        }
        return $entity;
    }
}

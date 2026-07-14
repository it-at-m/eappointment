<?php

namespace BO\Zmsbackend\Apikey\Service;

use BO\Zmsentities\Apiclient as Entity;

class Apiclient extends \BO\Zmsbackend\Base
{
    public static $cache = [];

    public function readEntity($clientKey)
    {
        $query = new \BO\Zmsbackend\Apikey\Repository\Apiclient(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences(0)
            ->addConditionApiclientKey($clientKey);
        $entity = $this->fetchOne($query, new Entity());
        return $entity;
    }
}

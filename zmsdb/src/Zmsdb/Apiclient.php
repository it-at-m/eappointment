<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Apiclient as Entity;

class Apiclient extends Base
{
    public static $cache = [];

    public function readEntity($clientKey)
    {
        $query = new Query\Apiclient(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences(0)
            ->addConditionApiclientKey($clientKey);
        $entity = $this->fetchOne($query, new Entity());
        return $entity;
    }
}

<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Provider as Entity;

class Provider extends Base
{
    public function readEntity($source, $providerId)
    {
        if ('dldb' !== $source) {
            return new Entity();
        }
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionProviderId($providerId);
        return $this->fetchOne($query, new Entity());
    }
}

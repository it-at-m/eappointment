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
    
    public function readList($source, $providerIds)
    {
        $query = new Query\Provider(Query\Base::SELECT);
        $query
        ->addEntityMapping();
        if (null !== $providerIds) {
            $query
            ->addConditionProviderCsv($providerIds);
        }
    
        return $this->fetchList($query, new Entity());
    }
}

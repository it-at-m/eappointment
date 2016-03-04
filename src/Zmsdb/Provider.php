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

    /**
     * TODO: Check if necessary, the list of providers should come by the calendar or process
     */
    public function readList($source, $providerIds)
    {
        if ('dldb' !== $source) {
            return [];
        }
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

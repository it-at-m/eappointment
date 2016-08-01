<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Provider as Entity;
use \BO\Zmsentities\Collection\ProviderList as Collection;

class Provider extends Base
{
    public function readEntity($source, $providerId, $resolveReferences = 0)
    {
        if ('dldb' !== $source) {
            return new Entity();
        }
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionProviderId($providerId);
        $provider = $this->fetchOne($query, new Entity());
        if ($resolveReferences > 0) {
            $provider['data'] = Helper\DldbData::readExtendedProviderData($source, $providerId);
        }
        return $provider;
    }

    public function readList($source, $isAssigned = false, $resolveReferences = 0)
    {
        $providerList = new Collection();
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionIsAssigned($isAssigned);
        $result = $this->fetchList($query, new Entity());
        foreach ($result as $provider) {
            if ($resolveReferences > 0) {
                $provider['data'] = Helper\DldbData::readExtendedProviderData($source, $provider['id']);
            }
            $providerList->addEntity($provider);
        }
        return $providerList;
    }

    public function readListByRequest($source, $requestIdCsv, $resolveReferences = 0)
    {
        $providerList = new Collection();
        if ('dldb' !== $source) {
            return $providerList;
        }
        $query = new Query\Provider(Query\Base::SELECT);
        $query->addEntityMapping();
        $query->addConditionRequestCsv($requestIdCsv);
        $result = $this->fetchList($query, new Entity());
        foreach ($result as $provider) {
            if ($resolveReferences > 0) {
                $provider['data'] = Helper\DldbData::readExtendedProviderData($source, $provider['id']);
            }
            $providerList->addEntity($provider);
        }
        return $providerList;
    }
}

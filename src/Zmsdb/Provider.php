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

    public function readList($source, $isAssigned = null, $resolveReferences = 0)
    {
        $providerList = new Collection();
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionIsAssigned($isAssigned);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $item) {
                $provider = $this->readEntity($source, $item['id'], $resolveReferences);
                $providerList->addProvider($provider);
            }
        }
        return $providerList;
    }

    public function readListByRequest($source, $requestIds, $resolveReferences = 0)
    {
        $query = Query\Request::getQuerySlots();
        $providerList = new Collection();
        if ('dldb' !== $source) {
            return $providerList;
        }

        $requestIds = \explode(',', $requestIds);
        foreach ($requestIds as $requestId) {
            $result = $this->getReader()->fetchAll($query, ['request_id' => $requestId]);
            foreach ($result as $item) {
                $provider = $this->readEntity($source, $item['provider__id'], $resolveReferences);
                $providerList->addProvider($provider);
            }
        }
        return $providerList;
    }
}

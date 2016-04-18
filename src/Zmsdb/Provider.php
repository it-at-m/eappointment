<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Provider as Entity;
use \BO\Zmsentities\Collection\ProviderList as Collection;

class Provider extends Base
{
    public function readEntity($source, $providerId, $resolveData = true)
    {
        if ('dldb' !== $source) {
            return new Entity();
        }
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionProviderId($providerId);
        $provider = $this->fetchOne($query, new Entity());
        if ($resolveData) {
            $provider['data'] = Helper\DldbData::readExtendedProviderData($source, $providerId);
        }
        return $provider;
    }

    public function readProviderByRequest($source, $requestIds, $resolveReferences = 0)
    {
        $providerList = new Collection();
        if ('dldb' !== $source) {
            return $providerList;
        }

        $requestIds = \explode(',', $requestIds);
        $providerIds = array();
        foreach ($requestIds as $requestId) {
            $request = (new Request())->readEntity($source, $requestId, 1);
            foreach ($request->getProviderIds() as $providerId) {
                $provider = $this->readEntity($source, $providerId, false);
                $providerList->addProvider($provider);
            }
        }
        return $providerList;
    }
}

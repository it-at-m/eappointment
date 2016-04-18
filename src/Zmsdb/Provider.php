<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Provider as Entity;
use \BO\Zmsentities\Collection\ProviderList as Collection;

class Provider extends Base
{
    public function readEntity($source, $providerId, $resolveReferences = 1)
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

    public function readProviderByRequest($source, $requestIds, $resolveReferences = 0)
    {
        $providerList = new Collection();
        if ('dldb' !== $source) {
            return $providerList;
        }

        $requestIds = \explode(',', $requestIds);
        foreach ($requestIds as $requestId) {
            $request = (new Request())->readEntity($source, $requestId, 1);
            foreach ($request->getProviderIds() as $providerId) {
                $provider = $this->readEntity($source, $providerId, $resolveReferences);
                $providerList->addProvider($provider);
            }
        }
        return $providerList;
    }
}

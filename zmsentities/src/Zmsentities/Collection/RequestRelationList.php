<?php

namespace BO\Zmsentities\Collection;

class RequestRelationList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\RequestRelation';

    public function hasRequest($requestIdCsv)
    {
        $requestIdCsv = explode(',', $requestIdCsv);
        foreach ($requestIdCsv as $requestId) {
            if (!in_array($requestId, $this->getRequestList()->getIds())) {
                return false;
            }
        }
        return true;
    }

    public function hasProvider($providerIdCsv)
    {
        $providerIdCsv = explode(',', $providerIdCsv);
        foreach ($providerIdCsv as $providerId) {
            if (!in_array($providerId, $this->getProviderList()->getIds())) {
                return false;
            }
        }
        return true;
    }

    public function getRequestList()
    {
        $requestList = new RequestList();
        foreach ($this as $item) {
            if (isset($item['request'])) {
                $entity = new \BO\Zmsentities\Request($item['request']);
                $requestList->addEntity($entity);
            }
        }
        return $requestList->withUniqueRequests();
    }

    public function getProviderList()
    {
        $providerList = new ProviderList();
        foreach ($this as $item) {
            if (isset($item['provider'])) {
                $entity = new \BO\Zmsentities\Provider($item['provider']);
                $providerList->addEntity($entity);
            }
        }
        return $providerList->withUniqueProvider();
    }

    public function getFilteredByRequestAndProvider($requestList, $providerList)
    {
        $list = new self();
        foreach ($requestList as $request) {
            foreach ($this as $item) {
                if (
                    $request->getId() == $item['request']['id'] &&
                    $providerList->hasProvider($item['provider']['id'])
                ) {
                    $list->addEntity($item);
                }
            }
        }
        return $list;
    }
}

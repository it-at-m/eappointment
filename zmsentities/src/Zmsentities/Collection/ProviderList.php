<?php

namespace BO\Zmsentities\Collection;

class ProviderList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Provider';

    public function hasProvider($providerIdCsv)
    {
        $providerFound = false;
        $providerIds = explode(',', $providerIdCsv);
        foreach ($providerIds as $providerId) {
            if (in_array($providerId, $this->getIds())) {
                $providerFound = true;
            }
        }
        return $providerFound;
    }

    public function hasProviderStrict($providerIdCsv)
    {
        $providerIds = explode(',', $providerIdCsv);
        foreach ($providerIds as $providerId) {
            if (! in_array($providerId, $this->getIds())) {
                return false;
            }
        }
        return true;
    }

    public function hasRequest($requestIdCsv)
    {
        $requestIds = explode(',', $requestIdCsv);
        foreach ($this as $entity) {
            foreach ($requestIds as $requestId) {
                if ($entity->hasRequest($requestId)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function withMatchingByList($providerIdCsv)
    {
        $collection = new self();
        $providerIds = explode(',', $providerIdCsv);
        foreach ($providerIds as $providerId) {
            if (in_array($providerId, $this->getIds())) {
                $collection->addEntity($this->getEntity($providerId));
            }
        }
        return $collection;
    }

    public function withUniqueProvider()
    {
        $list = new self();
        foreach ($this as $provider) {
            if ($provider && ! $list->hasEntity($provider->id)) {
                $list->addEntity($provider);
            }
        }
        return $list;
    }

    public function withDataAsObject()
    {
        $list = new self();
        foreach ($this as $provider) {
            if (isset($provider['data']) && '{}' != $provider->data) {
                if (is_string($provider->data)) {
                    $provider->data = json_decode($provider->data);
                } elseif (is_array($provider->data)) {
                    $provider->data = json_decode(json_encode($provider->data, JSON_FORCE_OBJECT));
                }
            } else {
                unset($provider['data']);
            }
            $list->addEntity($provider);
        }
        return $list;
    }

    public function sortById()
    {
        $list = clone $this;
        $list->uasort(function ($a, $b) {
            return ($a['id'] - $b['id']);
        });
        return (new self())->addList($list);
    }
}

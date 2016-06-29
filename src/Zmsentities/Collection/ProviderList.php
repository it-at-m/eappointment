<?php
namespace BO\Zmsentities\Collection;

class ProviderList extends Base
{

    public function addEntity($provider)
    {
        $this[] = clone $provider;
        return $this;
    }

    public function hasEntity($entityId)
    {
        foreach ($this as $entity) {
            if ($entityId == $entity->id) {
                return true;
            }
        }
        return false;
    }

    public function hasProvider($providerIdCsv)
    {
        $providerIdCsv = explode(',', $providerIdCsv);
        foreach ($providerIdCsv as $providerId) {
            if (in_array($providerId, $this->getIds())) {
                return true;
            }
        }
        return false;
    }

    public function getIds()
    {
        $list = array();
        foreach ($this as $provider) {
            $list[] = $provider['id'];
        }
        return $list;
    }
}

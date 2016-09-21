<?php
namespace BO\Zmsentities\Collection;

class ProviderList extends Base
{
    public function hasProvider($providerIdCsv)
    {
        $result = false;
        $providerIds = explode(',', $providerIdCsv);
        foreach ($this as $entity) {
            foreach ($providerIds as $providerId) {
                $result = $entity->hasId($providerId);
            }
        }
        return $result;
    }

    public function hasRequest($requestIdCsv)
    {
        $result = false;
        $requestIds = explode(',', $requestIdCsv);
        foreach ($this as $entity) {
            foreach ($requestIds as $requestId) {
                $result = $entity->hasRequest($requestId);
            }
        }
        return $result;
    }

    public function getIds()
    {
        $list = array();
        foreach ($this as $provider) {
            $list[] = $provider['id'];
        }
        return $list;
    }

    public function getIdsCsv()
    {
        return implode(',', $this->getIds());
    }
}

<?php
namespace BO\Zmsentities\Collection;

class ProviderList extends Base
{
    public function hasProvider($providerIdCsv)
    {
        $providerIdCsv = explode(',', $providerIdCsv);
        foreach ($providerIdCsv as $providerId) {
            if (!in_array($providerId, $this->getIds())) {
                error_log($providerId);
                return false;
            }
        }
        return true;
    }

    public function hasRequest($requestIdCsv)
    {
        $result = false;
        $requestIdCsv = explode(',', $requestIdCsv);
        foreach ($this as $entity) {
            foreach ($requestIdCsv as $requestId) {
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

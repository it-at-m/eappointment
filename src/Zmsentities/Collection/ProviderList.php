<?php
namespace BO\Zmsentities\Collection;

class ProviderList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Provider';

    public function hasProvider($providerIdCsv)
    {
        $providerIds = explode(',', $providerIdCsv);
        foreach ($providerIds as $providerId) {
            if (!in_array($providerId, $this->getIds())) {
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
}

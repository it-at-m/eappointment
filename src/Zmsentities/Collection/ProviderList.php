<?php
namespace BO\Zmsentities\Collection;

class ProviderList extends Base
{
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

<?php
namespace BO\Zmsentities\Collection;

class ProviderList extends Base
{
    public function hasProvider($providerIdCsv)
    {
        $result = true;
        $providerIdCsv = explode(',', $providerIdCsv);
        foreach ($providerIdCsv as $providerId) {
            if (!in_array($providerId, $this->getIds())) {
                $result = false;
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
}

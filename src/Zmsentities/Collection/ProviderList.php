<?php
namespace BO\Zmsentities\Collection;

class ProviderList extends BaseController
{

    public function addProvider($provider)
    {
        if ($provider instanceof \BO\Zmsentities\Provider) {
            $this[] = clone $provider;
        }
        return $this;
    }

    public function hasProvider($providerIds)
    {
        $result = true;
        foreach ($this as $provider) {
            if ($provider->hasProvider($providerIds)) {
                $result = false;
            }
        }
        return $result;
    }

}

<?php

namespace BO\Zmsentities;

class Provider extends Schema\Entity
{
    public static $schema = "provider.json";

    public function hasProvider($providerIds)
    {
        $providerIds = explode(',', $providerIds);
        if (isset($this->id)) {
            return (in_array($this->id, $providerIds)) ? true : false;
        }
    }
}

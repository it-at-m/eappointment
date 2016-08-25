<?php

namespace BO\Zmsentities;

class Provider extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "provider.json";

    public function hasRequest($requestId)
    {
        return in_array($requestId, $this['data']['services']);
    }
}

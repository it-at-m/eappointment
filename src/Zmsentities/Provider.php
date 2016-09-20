<?php

namespace BO\Zmsentities;

class Provider extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "provider.json";

    public function hasRequest($requestId)
    {
        $requests = $this->toProperty()->data->services->get();
        return ($requests) ? in_array($requestId, $requests) : false;
    }
}

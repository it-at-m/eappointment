<?php

namespace BO\Zmsentities;

class Request extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "request.json";

    public function withReference($additionalData = [])
    {
        $additionalData['id'] = $this['id'];
        $additionalData['name'] = $this['name'];
        return parent::withReference($additionalData);
    }
}

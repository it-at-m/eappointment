<?php

namespace BO\Zmsentities;

class Request extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "request.json";

    public function hasId()
    {
        return (array_key_exists('id', $this)) ? true : false;
    }
}

<?php

namespace BO\Zmsentities;

class Provider extends Schema\Entity
{
    public static $schema = "provider.json";

    public function hasId()
    {
        return (array_key_exists('id', $this)) ? true : false;
    }
}

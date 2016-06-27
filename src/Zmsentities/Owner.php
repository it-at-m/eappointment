<?php

namespace BO\Zmsentities;

class Owner extends Schema\Entity
{
    public static $schema = "owner.json";

    public function hasId()
    {
        return (array_key_exists('id', $this)) ? true : false;
    }
}

<?php

namespace BO\Zmsentities;

class Organisation extends Schema\Entity
{
    public static $schema = "organisation.json";

    public function hasId()
    {
        return (array_key_exists('id', $this)) ? true : false;
    }

    public function getPreference($name)
    {
        if (array_key_exists('preferences', $this) && array_key_exists($name, $this->preferences)) {
            return $this->preferences[$name];
        }
        return null;
    }
}

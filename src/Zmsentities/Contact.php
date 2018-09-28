<?php

namespace BO\Zmsentities;

class Contact extends Schema\Entity
{
    public static $schema = "contact.json";

    public function getProperty($propertyName, $default = '')
    {
        return ($this->hasProperty($propertyName)) ? $this->toProperty()->{$propertyName}->get() : $default;
    }
}

<?php

namespace BO\Zmsentities;

class Contact extends Schema\Entity
{
    public static $schema = "contact.json";

    public function getProperty($propertyName)
    {
        return $this->toProperty()->{$propertyName}->get();
    }
}

<?php

namespace BO\Zmsentities;

class Mailtemplate extends Schema\Entity
{
    public static $schema = "mailtemplate.json";

    public function getDefaults()
    {
        return [
        ];
    }

    public function getNotificationPreferences()
    {
        return $this->toProperty()->notifications->get();
    }

    public function hasType($type)
    {
        return (isset($this[$type])) ? true : false;
    }

    public function hasPreference($type, $key)
    {
        return ($this->hasType($type) && isset($this[$type][$key])) ? true : false;
    }

    public function getPreference($type, $key)
    {
        return $this->toProperty()->$type->$key->get();
    }

    public function setPreference($type, $key, $value)
    {
        $preference = $this->toProperty()->$type->$key->get();
        if (null !== $preference) {
            $this[$type][$key] = $value;
        }
        return $this;
    }

}

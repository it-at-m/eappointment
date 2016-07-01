<?php

namespace BO\Zmsentities;

class Workstation extends Schema\Entity
{
    public static $schema = "workstation.json";

    public function hasId()
    {
        return (array_key_exists('id', $this)) ? true : false;
    }

    public function getQueuePreference($key, $isBoolean = false)
    {
        if (array_key_exists($key, $this)) {
            if ($isBoolean) {
                return ($this[$key]) ? 1 : 0;
            } else {
                return $this[$key];
            }
        }
    }

    public function getAuthKey()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}

<?php

namespace BO\Zmsentities;

class Useraccount extends Schema\Entity
{
    public static $schema = "useraccount.json";

    public function hasId()
    {
        return (array_key_exists('id', $this)) ? true : false;
    }

    public function testRights()
    {
        if ($this->hasId()) {
            $requiredRights = func_get_args();
            foreach ($requiredRights as $required) {
                if (!array_key_exists($required, array_filter($this->rights))) {
                    throw new Exception\UserAccountMissingRights();
                }
            }
        } else {
            throw new Exception\UserAccountMissingLogin();
        }
        return $this;
    }
}

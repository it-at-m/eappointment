<?php

namespace BO\Zmsentities;

class Department extends Schema\Entity
{
    public static $schema = "department.json";

    public function hasNotificationEnabled()
    {
        return ($this->preferences['notifications']['enabled']) ? true : false;
    }

    public function getNotificationPreferences()
    {
        return ($this->preferences['notifications']);
    }
}
